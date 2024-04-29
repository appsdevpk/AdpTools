<?php
/** @noinspection UnknownInspectionInspection
 * @noinspection PhpUnused
 */

namespace eftec;

use RuntimeException;

/**
 * Class MessageLocker
 *
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @version       2.8 2024-03-02
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/MessageContainer
 * @see           https://github.com/EFTEC/MessageContainer
 */
class MessageLocker
{
    /**
     * @var array|null It is an associative array with the context of the locker.<br>
     *            The context is only set once, it is for optimization. So, if the contexts contains information
     *            (not null) then it is not updated.
     */
    private ?array $context = null;
    /** @var mixed|null The id of the locker */
    private $idLocker;
    /** @var string[] */
    private array $errorMsg = [];
    /** @var string[] */
    private array $warningMsg = [];
    /** @var string[] */
    private array $infoMsg = [];
    /** @var string[] */
    private array $successMsg = [];

    /**
     * MessageLocker constructor.
     * @param null|string $idLocker
     * @param array|null  $context
     */
    public function __construct(?string $idLocker = null, ?array &$context = null)
    {
        $this->idLocker = $idLocker;
        $this->resetAll();
        $this->setContext($context);
    }

    /**
     * It clears all the messages. However, it doesn't recounter the MessageContainer
     * @return void
     */
    public function resetAll(): void
    {
        $this->errorMsg = [];
        $this->warningMsg = [];
        $this->infoMsg = [];
        $this->successMsg = [];
    }

    /**
     * We set the context only if the current context is null.
     *
     * @param array|null $context The new context.
     */
    public function setContext(?array &$context): void
    {
        if ($this->context === null) {
            $this->context =& $context;
        }
    }

    /**
     * It adds an error to the locker.
     *
     * @param string|null $msg The message to store
     * @return string|null returns the last message (processed)
     */
    public function addError(?string $msg): ?string
    {
        $msg = $this->replaceCurlyVariable($msg);
        $this->errorMsg[] = $msg;
        return $msg;
    }

    /**
     * Replaces all variables defined between {{ }} by a variable inside the dictionary of values.<br>
     * Example:<br>
     *      replaceCurlyVariable('hello={{var}}',['var'=>'world']) // hello=world<br>
     *      replaceCurlyVariable('hello={{var}}',['varx'=>'world']) // hello=<br>
     *      replaceCurlyVariable('hello={{var}}',['varx'=>'world'],true) // hello={{var}}<br>
     *
     * @param string|null $string The input value. It could contain variables defined as {{namevar}}
     * @return string|null
     * @see https://github.com/EFTEC/mapache-commons
     */
    public function replaceCurlyVariable(?string $string): ?string
    {
        if($string===null) {
            return null;
        }
        if (strpos($string, '{{') === false) {
            return $string; // nothing to replace.
        }
        $string = str_replace('{{_idlocker}}', $this->idLocker, $string);
        return preg_replace_callback('/{{\s?(\w+)\s?}}/u', function ($matches) {
            if (is_array($matches)) {
                $item = substr($matches[0], 2, -2); // removes {{ and }}
                return $this->context[$item] ?? '';
            }
            $item = substr($matches, 2, -2); // removes {{ and }}
            return $this->context[$item] ?? '';
        }, $string);
    }

    /**
     * It adds a warning to the locker.
     *
     * @param string|null $msg The message to store
     * @return string|null returns the last message (processed)
     */
    public function addWarning(?string $msg): ?string
    {
        $msg = $this->replaceCurlyVariable($msg);
        $this->warningMsg[] = $msg;
        return $msg;
    }

    /**
     * It adds an information to the locker.
     *
     * @param string|null $msg The message to store
     * @return string|null returns the last message (processed)
     */
    public function addInfo(?string $msg): ?string
    {
        $this->infoMsg[] = $this->replaceCurlyVariable($msg);
        return $msg;
    }

    /**
     * It adds a success to the locker.
     *
     * @param string|null $msg The message to store
     * @return string|null returns the last message (processed)
     */
    public function addSuccess(?string $msg): ?string
    {
        $this->successMsg[] = $this->replaceCurlyVariable($msg);
        return $msg;
    }

    /**
     * It returns the number of errors or warnings contained in the locker
     *
     * @return int
     */
    public function countErrorOrWarning(): int
    {
        return $this->countError() + $this->countWarning();
    }

    /**
     * It returns the number of errors contained in the locker
     *
     * @return int
     */
    public function countError(): int
    {
        return count($this->errorMsg);
    }

    /**
     * It returns the number of warnings contained in the locker
     *
     * @return int
     */
    public function countWarning(): int
    {
        return count($this->warningMsg);
    }

    /**
     * It returns the number of infos contained in the locker
     *
     * @return int
     */
    public function countInfo(): int
    {
        return count($this->infoMsg);
    }

    /**
     * It returns the number of successes contained in the locker
     *
     * @return int
     */
    public function countSuccess(): int
    {
        return count($this->successMsg);
    }

    /**
     * Returns the number of messages per level.
     * @param string $level =['*','error','warning','errorwarning','info','success'][$i]
     * @return int
     */
    public function count(string $level):int
    {
        switch ($level) {
            case '*':
                return $this->countError()+$this->countWarning()+$this->countInfo()+$this->countSuccess();
            case 'error':
                return $this->countError();
            case 'errorwarning':
                return $this->countErrorOrWarning();
            case 'warning':
                return $this->countWarning();
            case 'info':
                return $this->countInfo();
            case 'success':
                return $this->countSuccess();
        }
        throw new RuntimeException("count with wrong type [$level]",22);
    }

    /**
     * It returns the first message of any kind.<br>
     * If error then it returns the first message of error<br>
     * If not, if warning then it returns the first message of warning<br>
     * If not, then it shows the first info message (if any)<br>
     * If not, then it shows the first success message (if any)<br>
     * If not, then it shows the default message.
     *
     * @param string      $defaultMsg
     * @param null|string $level =[null,'error','warning','errorwarning','info','success'][$i] the level to show,by
     *                           default it shows the first message of any level
     *                           , starting with error.
     * @return string
     */
    public function first(string $defaultMsg = '', ?string $level = null): ?string
    {
        switch ($level) {
            case 'error':
                return $this->firstError($defaultMsg);
            case 'warning':
                return $this->firstWarning($defaultMsg);
            case 'errorwarning':
                return $this->firstErrorOrWarning($defaultMsg);
            case 'info':
                return $this->firstInfo($defaultMsg);
            case 'success':
                return $this->firstSuccess($defaultMsg);
        }
        $r = $this->firstErrorOrWarning();
        if ($r !== null) {
            return $r;
        }
        $r = $this->firstInfo();
        if ($r !== null) {
            return $r;
        }
        $r = $this->firstSuccess();
        return $r ?? $defaultMsg;
    }

    /**
     * It returns the last message of any kind.<br>
     * If error then it returns the last message of error<br>
     * If not, if warning then it returns the last message of warning<br>
     * If not, then it shows the last info message (if any)<br>
     * If not, then it shows the last success message (if any)<br>
     * If not, then it shows the default message.
     *
     * @param string $defaultMsg
     * @param string $level      =['*','error','warning','errorwarning','info','success'][$i]<br>
     *                           The level to show.<br>
     *                           By default it shows the last message of any level, starting with error.
     * @return string
     */
    public function last(string $defaultMsg = '', string $level = '*'): ?string
    {
        switch ($level) {
            case 'error':
                return $this->lastError($defaultMsg);
            case 'warning':
                return $this->lastWarning($defaultMsg);
            case 'errorwarning':
                return $this->lastErrorOrWarning($defaultMsg);
            case 'info':
                return $this->lastInfo($defaultMsg);
            case 'success':
                return $this->lastSuccess($defaultMsg);
            case '*':
                $r = $this->lastErrorOrWarning();
                if ($r !== null) {
                    return $r;
                }
                $r = $this->lastInfo();
                if ($r !== null) {
                    return $r;
                }
                $r = $this->lastSuccess();
                return $r ?? $defaultMsg;
        }
        throw new RuntimeException("MessageLocker::last, method $level not defined",8);
    }

    /**
     * It returns the first message of error, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function firstError(?string $default = null): ?string
    {
        return $this->errorMsg[0] ?? $default;
    }

    /**
     * It returns the last message of error, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function lastError(?string $default = null): ?string
    {
        return end($this->errorMsg) ?: $default;
    }

    /**
     * It returns the first message of warning, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function firstWarning(?string $default = null): ?string
    {
        return $this->warningMsg[0] ?? $default;
    }

    /**
     * It returns the last message of error, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function lastWarning(?string $default = null): ?string
    {
        return end($this->warningMsg) ?: $default;
    }

    /**
     * It returns the first message of error or warning (in this order), if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function firstErrorOrWarning(?string $default = null): ?string
    {
        $r = $this->firstError();
        if ($r === null) {
            $r = $this->firstWarning();
        }
        return $r ?? $default;
    }

    /**
     * It returns the first message of error or warning (in this order), if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function lastErrorOrWarning(?string $default = null): ?string
    {
        $r = $this->lastError();
        if ($r === null) {
            $r = $this->lastWarning();
        }
        return $r ?? $default;
    }

    /**
     * It returns the first message of info, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function firstInfo(?string $default = null): ?string
    {
        return $this->infoMsg[0] ?? $default;
    }

    /**
     * It returns the last message of error, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function lastInfo(?string $default = null): ?string
    {
        return end($this->infoMsg) ?: $default;
    }

    /**
     * It returns the first message of success, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function firstSuccess(?string $default = null): ?string
    {
        return $this->successMsg[0] ?? $default;
    }

    /**
     * It returns the last message of error, if any. Otherwise, it returns the default value
     *
     * @param string|null $default (optional) The default value is not found
     *
     * @return null|string
     */
    public function lastSuccess(?string $default = null): ?string
    {
        return end($this->successMsg) ?: $default;
    }

    /**
     * Returns all messages or an empty array if none.
     *
     * @param null|string $level =[null,'error','warning','errorwarning','info','success'][$i] the level to show. Null
     *                           means it shows all errors
     * @return string[]
     */
    public function all(?string $level = null): array
    {
        switch ($level) {
            case 'error':
                return $this->allError();
            case 'warning':
                return $this->allWarning();
            case 'errorwarning':
                return $this->allErrorOrWarning();
            case 'info':
                return $this->allInfo();
            case 'success':
                return $this->allSuccess();
        }
        return @array_merge($this->errorMsg, $this->warningMsg, $this->infoMsg, $this->successMsg);
    }

    /**
     * Returns all messages of errors (as an array of string), or an empty array if none.
     *
     * @return string[]
     */
    public function allError(): array
    {
        return $this->errorMsg;
    }

    /**
     * Returns all messages of warning, or an empty array if none.
     *
     * @return string[]
     */
    public function allWarning(): array
    {
        return $this->warningMsg;
    }

    /**
     * Returns all messages of errors or warnings, or an empty array if none
     *
     * @return string[]
     */
    public function allErrorOrWarning(): array
    {
        return @array_merge($this->errorMsg, $this->warningMsg);
    }

    /**
     * Returns all messages of info, or an empty array if none.
     *
     * @return string[]
     */
    public function allInfo(): array
    {
        return $this->infoMsg;
    }

    /**
     * Returns all messages of success, or an empty array if none.
     *
     * @return string[]
     */
    public function allSuccess(): array
    {
        return $this->successMsg;
    }

    /**
     * It returns an associative array of the form:<br>
     * <pre>
     * [
     *  ['id'=>'', // id of the locker
     *  'level'=>'' // level of message (error, warning, info or success)
     *  'msg'=>'' // the message to show
     *  ]
     * ]
     * </pre>
     *
     * @param null|string $level    =[null,'error','warning','errorwarning','info','success'][$i] the level to show.
     *                              Null means it shows all messages regardless of the level (starting with error)
     * @return array
     */
    public function allAssocArray(?string $level = null): array
    {
        $result = [];
        if ($level === 'error' || $level === 'errorwarning' || $level === null) {
            $tmp = $this->allError();
            foreach ($tmp as $vmsg) {
                $result[] = ['id' => $this->idLocker, 'level' => 'error', 'msg' => $vmsg];
            }
        }
        if ($level === 'warning' || $level === 'errorwarning' || $level === null) {
            $tmp = $this->allWarning();
            foreach ($tmp as $vmsg) {
                $result[] = ['id' => $this->idLocker, 'level' => 'warning', 'msg' => $vmsg];
            }
        }
        if ($level === 'info' || $level === null) {
            $tmp = $this->allInfo();
            foreach ($tmp as $vmsg) {
                $result[] = ['id' => $this->idLocker, 'level' => 'info', 'msg' => $vmsg];
            }
        }
        if ($level === 'success' || $level === null) {
            $tmp = $this->allSuccess();
            foreach ($tmp as $vmsg) {
                $result[] = ['id' => $this->idLocker, 'level' => 'success', 'msg' => $vmsg];
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
            ? count($this->errorMsg)
            : count($this->errorMsg) + count($this->warningMsg);
        return $tmp !== 0;
    }
}
