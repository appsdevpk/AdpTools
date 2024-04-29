<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection SlowArrayOperationsInLoopInspection */
/** @noinspection PhpUnused */

namespace eftec;

use RuntimeException;

/**
 * Class MessageList
 *
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @version       2.9 2024-03-02
 * @copyright (c) Jorge Castro C. mit License  https://github.com/EFTEC/MessageContainer
 * @see           https://github.com/EFTEC/MessageContainer
 */
class MessageContainer
{
    /** @var  MessageLocker[] Array of containers */
    public array $items = [];
    /** @var int Number of errors stored globally */
    public int $errorCount = 0;
    /** @var int Number of warnings stored globally */
    public int $warningCount = 0;
    /** @var int Number of errors or warning stored globally */
    public int $errorOrWarningCount = 0;
    /** @var int Number of information stored globally */
    public int $infoCount = 0;
    /** @var int Number of success stored globally */
    public int $successCount = 0;
    /** @var string[] Used to convert a type of message to a css class */
    public array $cssClasses = ['error' => 'danger', 'warning' => 'warning', 'info' => 'info', 'success' => 'success'];
    protected bool $throwOnError = false;
    protected bool $throwOnWarning = false;
    protected bool $logOnError = false;
    protected bool $logOnWarning = false;
    protected bool $logOnInfo = false;
    protected bool $logOnSuccess = false;
    /** @var string|null the filename to log. If no file, then it uses the default file specified on error_log */
    protected ?string $logFilename = null;
    protected array $backupLog = [false, false, false, false];
    /** @var null|MessageContainer singleton */
    protected static ?MessageContainer $instance = null;

    /**
     * MessageList constructor.
     */
    public function __construct($setSingleton = true)
    {
        $this->items = [];
        if ($setSingleton) {
            self::$instance = $this;
        }
    }

    /**
     * Obtain the singleton and if it doesn't exist, then it is created.
     * @return MessageContainer
     */
    public static function instance(): MessageContainer
    {
        if (self::$instance === null) {
            self::$instance = new MessageContainer(false);
        }
        return self::$instance;
    }

    /**
     * It resets all the container and flush all the results.
     */
    public function resetAll(): void
    {
        $this->errorCount = 0;
        $this->warningCount = 0;
        $this->errorOrWarningCount = 0;
        $this->infoCount = 0;
        $this->successCount = 0;
        $this->items = [];
        $this->throwOnError = false;
        $this->throwOnWarning = false;
        $this->logOnError = false;
        $this->logOnWarning = false;
        $this->logOnInfo = false;
        $this->logOnSuccess = false;
    }

    /**
     * It resets (clear the messages) of a specific locker by deleting all the messages and keeping
     * the count of them.<br>
     * @param string $idLocker If the locker doesn't exist, it does nothing.
     * @return void
     */
    public function resetLocker(string $idLocker): void
    {
        if (!$this->hasLocker($idLocker)) {
            return;
        }
        $locker = $this->get($idLocker);
        $this->errorCount -= $locker->countError();
        $this->warningCount -= $locker->countWarning();
        $this->infoCount -= $locker->countInfo();
        $this->successCount -= $locker->countSuccess();
        $locker->resetAll();
    }

    /**
     * Log a message using the error_log system.<br>
     * If level is error, then it logs directly<br>
     * If level is not error, then it logs in a different file (a file with a prefix)<br>
     * The filename is determined using the current error_log file.<br>
     * If it is unable to determine, then it stores in /var/log (Linux/MacOs) or in session.save_path (Windows)
     * @param string      $level =['error','warning','info','success'][$i]
     * @param string|null $txt   The message to log
     * @return bool
     * @noinspection ForgottenDebugOutputInspection
     */
    public function log(string $level, ?string $txt): bool
    {
        if ($level === 'error') {
            return error_log($txt);
        }
        return error_log('[' . date("d-M-Y H:i:s e") . "] " . $txt . "\n", 3, $this->getLogFilename($level));
    }

    /**
     * It sets the filename to log file.
     * @param string $filename
     * @return void
     */
    public function setLogFilename(string $filename): MessageContainer
    {
        $this->logFilename = $filename;
        return $this;
    }

    public function getLogFilename(string $level = 'error'): string
    {
        $original = $this->logFilename ?? ini_get('error_log'); // by default this value is empty.
        $postfix = ($level === 'error') ? '' : '_' . $level; // error does not generate a postfix
        if ($original) { // php has a log file set
            $pos = strrpos($original, '.');
            if ($pos !== false) {
                $original = substr_replace($original, $postfix . '.', $pos, strlen('.'));
            } else {
                $original .= $postfix;
            }
            return $original;
        }
        // php does not have a log file, so we use a default path. On Windows, we use the session path.
        if (PHP_OS_FAMILY === 'Windows') {
            $path = ini_get('session.save_path');
            if (!$path) {
                $path = $_SERVER['APPDATA'];
            }
        } else {
            $path = '/var/log';
        }
        return $path . "/php_error$postfix.log";
    }

    /**
     * If we store an error then we also throw a PHP exception.
     *
     * @param bool    $throwOnError   if true (default), then it throws an excepcion every time
     *                                we store an error.
     * @param boolean $includeWarning If true then it also includes warnings.
     * @return MessageContainer
     */
    public function throwOnError(bool $throwOnError = true, bool $includeWarning = false): MessageContainer
    {
        $this->throwOnError = $throwOnError;
        $this->throwOnWarning = $includeWarning;
        return $this;
    }

    /**
     * If we store a message then we also could write the information in a file using error_log
     *
     * @param bool    $logOnError   if true (default), then it saves the log file (using error_log)
     * @param boolean $logOnWarning If true then it also includes the log with warnings (default false).
     * @param bool    $logOnInfo    If true then it also includes the log with info (default false).
     * @param bool    $logOnSuccess If true then it also includes the log with success (default false).
     * @return MessageContainer
     */
    public function LogOnError(bool $logOnError = true, bool $logOnWarning = false
        , bool                      $logOnInfo = false, bool $logOnSuccess = false): MessageContainer
    {
        $this->logOnError = $logOnError;
        $this->logOnWarning = $logOnWarning;
        $this->logOnInfo = $logOnInfo;
        $this->logOnSuccess = $logOnSuccess;
        return $this;
    }

    /**
     * Alias of LongOnError()
     * @param bool    $logOnError   if true (default), then it saves the log file (using error_log)
     * @param boolean $logOnWarning If true then it also includes the log with warnings (default false).
     * @param bool    $logOnInfo    If true then it also includes the log with info (default false).
     * @param bool    $logOnSuccess If true then it also includes the log with success (default false).
     * @return $this
     * @see MessageContainer::LogOnError
     */
    public function setLog(bool $logOnError = true, bool $logOnWarning = false
        , bool                  $logOnInfo = false, bool $logOnSuccess = false): self
    {
        return $this->logOnError($logOnError, $logOnWarning, $logOnInfo, $logOnSuccess);
    }

    /**
     * It returns an indexed array with the values of when the library must logs.
     * @return array [logOnError,logOnWarning,logOnInfo,logOnSuccess]
     */
    public function getLog(): array
    {
        return [$this->logOnError, $this->logOnWarning, $this->logOnInfo, $this->logOnSuccess];
    }

    public function backupLog(): MessageContainer
    {
        $this->backupLog = $this->getLog();
        return $this;
    }

    public function restoreLog(): MessageContainer
    {
        $this->setLog(...$this->backupLog);
        return $this;
    }

    /**
     * You could add a message (including errors,warning, etc.) and store it in a $idLocker
     *
     * @param string     $idLocker Identified of the locker (where the message will be stored)
     * @param string     $message  message to show. Example: 'the value is incorrect'.<br>
     *                             You can also use variables (if you are set a context). Ex: {{var1}} <br>
     *                             You can also show the idlocker. Ex: {{_idlocker}}<br>
     * @param string     $level    =['error','warning','info','success'][$i]
     * @param array|null $context  [optional] it is an associative array with the values of the item<br>
     *                             For optimization, the context is not update if exists another context.
     */
    public function addItem(string $idLocker, string $message, string $level = 'error', ?array $context = null): void
    {
        $idLocker = ($idLocker === '') ? '0' : $idLocker;
        if (!isset($this->items[$idLocker])) {
            $this->items[$idLocker] = new MessageLocker($idLocker, $context);
        } else {
            $this->items[$idLocker]->setContext($context);
        }
        // if the message contains a curly braces, then it is convert using the context.
        switch ($level) {
            case 'error':
                $this->errorCount++;
                $this->errorOrWarningCount++;
                $lastmsg = $this->items[$idLocker]->addError($message);
                if ($this->logOnError) {
                    $this->log($level, $lastmsg);
                }
                if ($this->throwOnError) {
                    throw new RuntimeException($lastmsg, 1);
                }
                break;
            case 'warning':
                $this->warningCount++;
                $this->errorOrWarningCount++;
                $this->items[$idLocker]->addWarning($message);
                if ($this->logOnWarning) {
                    $this->log($level, $message);
                }
                if ($this->throwOnWarning) {
                    throw new RuntimeException($message, 2);
                }
                break;
            case 'info':
                $this->infoCount++;
                $lastmsg = $this->items[$idLocker]->addInfo($message);
                if ($this->logOnInfo) {
                    $this->log($level, $lastmsg);
                }
                break;
            case 'success':
                $this->successCount++;
                $lastmsg = $this->items[$idLocker]->addSuccess($message);
                if ($this->logOnSuccess) {
                    $this->log($level, $lastmsg);
                }
                break;
        }
    }

    /**
     * It obtains all the ids for all the lockers.
     *
     * @return array
     */
    public function allIds(): array
    {
        return array_keys($this->items);
    }

    /**
     * Alias of $this->getMessage()
     * @param string $idLocker ID of the locker
     * @return MessageLocker
     */
    public function get(string $idLocker): MessageLocker
    {
        return $this->getLocker($idLocker);
    }

    /**
     * It returns a MessageLocker containing a locker.<br>
     * <b>If the locker doesn't exist then it returns an empty object (not null)</b>
     *
     * @param string $idLocker ID of the locker
     *
     * @return MessageLocker
     */
    public function getLocker(string $idLocker = ''): MessageLocker
    {
        $idLocker = ($idLocker === '') ? '0' : $idLocker;
        return $this->items[$idLocker] ?? new MessageLocker($idLocker);
    }

    /**
     * returns true if the locker is defined
     * @param string $idLocker
     * @return bool
     */
    public function hasLocker(string $idLocker): bool
    {
        return isset($this->items[$idLocker]);
    }

    /**
     * It returns a css class associated with the type of errors inside a locker<br>
     * If the locker contains more than one message, then it uses the most severe one (error,warning,etc.)<br>
     * The method uses the field <b>$this->cssClasses</b>, so you can change the CSS classes.
     * <pre>
     * $this->clsssClasses=['error'=>'class-red','warning'=>'class-yellow','info'=>'class-green','success'=>'class-blue'];
     * $css=$this->cssClass('customerId');
     * </pre>
     *
     * @param string $idLocker ID of the locker
     *
     * @return string
     */
    public function cssClass(string $idLocker): string
    {
        $idLocker = ($idLocker === '') ? '0' : $idLocker;
        if (!isset($this->items[$idLocker])) {
            return '';
        }
        if (@$this->items[$idLocker]->countError()) {
            return $this->cssClasses['error'];
        }
        if ($this->items[$idLocker]->countWarning()) {
            return $this->cssClasses['warning'];
        }
        if ($this->items[$idLocker]->countInfo()) {
            return $this->cssClasses['info'];
        }
        if ($this->items[$idLocker]->countSuccess()) {
            return $this->cssClasses['success'];
        }
        return '';
    }

    /**
     * It returns the first message of error or empty if none<br>
     * If not, then it returns the first message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     * @see MessageContainer::firstErrorText
     */
    public function firstErrorOrWarning(string $default = ''): string
    {
        return $this->firstErrorText($default, true);
    }

    /**
     * It returns the first message of error or empty if none<br>
     * If not, then it returns the first message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     * @see MessageContainer::firstErrorText
     */
    public function lastErrorOrWarning(string $default = ''): string
    {
        $r = $this->allErrorArray(true, 'last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of error (as text) or empty if none
     *
     * @param string $default        if not message is found, then it returns this value.
     * @param bool   $includeWarning if true then it also includes warning but any error has priority.
     * @return string empty or default if there is none
     */
    public function firstErrorText(string $default = '', bool $includeWarning = false): string
    {
        $r = $this->allErrorArray($includeWarning, 'first');
        return $r[0] ?? $default;
    }

    /**
     * It returns the last message of error (as text) or empty if none
     *
     * @param string $default        if not message is found, then it returns this value.
     * @param bool   $includeWarning if true then it also includes warning but any error has priority.
     * @return string empty (or default if there is none
     */
    public function lastErrorText(string $default = '', bool $includeWarning = false): string
    {
        $r = $this->allErrorArray($includeWarning, 'last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function firstWarningText(string $default = ''): string
    {
        $r = $this->allWarningArray('first');
        return $r[0] ?? $default;
    }

    /**
     * It returns the last message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function lastWarningText(string $default = ''): string
    {
        $r = $this->allWarningArray('last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of information or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function firstInfoText(string $default = ''): string
    {
        $r = $this->allInfoArray('first');
        return $r[0] ?? $default;
    }

    /**
     * It returns the last message of information or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function lastInfoText(string $default = ''): string
    {
        $r = $this->allInfoArray('last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of success or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function firstSuccessText(string $default = ''): string
    {
        $r = $this->allSuccessArray('first');
        return $r[0] ?? $default;
    }

    /**
     * It returns the last message of success or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function lastSuccessText(string $default = ''): string
    {
        $r = $this->allSuccessArray('last');
        return $r[0] ?? $default;
    }

    /**
     * It returns an array with all messages of any type of all lockers
     *
     * @param string|null $level =[null,'error','warning','errorwarning','info','success'][$i] the level to show.<br>
     *                           Null means it shows all errors
     * @return string[] empty array if there is none
     */
    public function allArray(?string $level = null): array
    {
        switch ($level) {
            case 'error':
                return $this->allErrorArray();
            case 'warning':
                return $this->allWarningArray();
            case 'errorwarning':
                return $this->allErrorOrWarningArray();
            case 'info':
                return $this->allInfoArray();
            case 'success':
                return $this->allSuccessArray();
        }
        $r = [];
        foreach ($this->items as $v) {
            $r = array_merge($r, $v->allError());
            $r = array_merge($r, $v->allWarning());
            $r = array_merge($r, $v->allInfo());
            $r = array_merge($r, $v->allSuccess());
        }
        return $r;
    }

    /**
     * It returns an array with all messages of error of all lockers.
     *
     * @param bool   $includeWarning if true then it also includes warnings.
     * @param string $position       =['*','first','last'][$i] the value to read<br>
     *                               * = reads all the errors (or warnings)<br>
     *                               first = reads the first error (or warning)<br>
     *                               last = reads the last error (or warning)<br>
     *
     * @return array empty if there is none
     */
    public function allErrorArray(bool $includeWarning = false, string $position = '*'): array
    {
        $r = [];
        if ($includeWarning) {
            foreach ($this->items as $v) {
                switch ($position) {
                    case '*':
                        $r = array_merge($r, $v->allErrorOrWarning());
                        break;
                    case 'first':
                        $tmp = $v->firstErrorOrWarning();
                        if ($tmp !== null) {
                            return [$tmp];
                        }
                        break;
                    case 'last':
                        $tmp = $v->lastErrorOrWarning();
                        if ($tmp !== null) {
                            $r[] = $tmp;
                        }
                        break;
                    default:
                        throw new RuntimeException("MessageContainer::allErrorArray unknow type $position", 3);
                }
            }
            if ($position === 'last') {
                return end($r) ? [end($r)] : [];
            }
            return $r;
        }
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allError());
                    break;
                case 'first':
                    $tmp = $v->firstError();
                    if ($tmp !== null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp = $v->lastError();
                    if ($tmp !== null) {
                        $r[] = $tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allErrorArray unknow type $position", 4);
            }
        }
        return $r;
    }

    /**
     * It returns an array with all messages of warning of all lockers.
     *
     * @return string[] empty array if there is none
     */
    public function allWarningArray($position = '*'): array
    {
        $r = [];
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allWarning());
                    break;
                case 'first':
                    $tmp = $v->firstWarning();
                    if ($tmp !== null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp = $v->lastWarning();
                    if ($tmp !== null) {
                        $r[] = $tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allWarningArray unknow type $position", 5);
            }
        }
        if ($position === 'last') {
            return end($r) ? [end($r)] : [];
        }
        return $r;
    }

    /**
     * It returns an array with all messages of errors and warnings of all lockers.
     *
     * @return string[] empty array if there is none
     * @see MessageContainer::allErrorArray
     */
    public function allErrorOrWarningArray(): array
    {
        return $this->allErrorArray(true);
    }

    /**
     * It returns an array with all messages of info of all lockers.
     *
     * @return string[] empty array if there is none
     */
    public function allInfoArray($position = '*'): array
    {
        $r = [];
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allInfo());
                    break;
                case 'first':
                    $tmp = $v->firstInfo();
                    if ($tmp !== null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp = $v->lastInfo();
                    if ($tmp !== null) {
                        $r[] = $tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allInfoArray unknow type $position", 6);
            }
        }
        if ($position === 'last') {
            return end($r) ? [end($r)] : [];
        }
        return $r;
    }

    /**
     * It returns an array with all messages of success of all lockers.
     *
     * @return string[] empty array if there is none
     */
    public function allSuccessArray($position = '*'): array
    {
        $r = [];
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allSuccess());
                    break;
                case 'first':
                    $tmp = $v->firstSuccess();
                    if ($tmp !== null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp = $v->lastSuccess();
                    if ($tmp !== null) {
                        $r[] = $tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allSuccessArray unknow type $position", 7);
            }
        }
        if ($position === 'last') {
            return end($r) ? [end($r)] : [];
        }
        return $r;
    }

    /**
     * It returns an associative array of the form <br>
     * <pre>
     * [
     *  ['id'=>'', // ID of the locker
     *  'level'=>'' // level of message (error, warning, info or success)
     *  'msg'=>'' // the message to show
     *  ]
     * ]
     * </pre>
     *
     * @param string $level =['*','error','warning','errorwarning','info','success'][$i] '*' (default means all levels).
     * @return array
     */
    public function allAssocArray(string $level = '*'): array
    {
        $result = [];
        foreach ($this->items as $v) {
            if ($level === 'error' || $level === 'errorwarning' || $level === '*') {
                $tmp = $v->allAssocArray('error');
                $result = array_merge($result, $tmp);
            }
            if ($level === 'warning' || $level === 'errorwarning' || $level === '*') {
                $tmp = $v->allAssocArray('warning');
                $result = array_merge($result, $tmp);
            }
            if ($level === 'info' || $level === '*') {
                $tmp = $v->allAssocArray('info');
                $result = array_merge($result, $tmp);
            }
            if ($level === 'success' || $level === '*') {
                $tmp = $v->allAssocArray('success');
                $result = array_merge($result, $tmp);
            }
        }
        return $result;
    }

    /**
     * It returns true if there is an error (or error and warning).
     *
     * @param bool $includeWarning If true then it also returns if there is a warning
     * @return bool
     */
    public function hasError(bool $includeWarning = false): bool
    {
        $tmp = $includeWarning
            ? $this->errorCount
            : $this->errorOrWarningCount;
        return $tmp !== 0;
    }
}
