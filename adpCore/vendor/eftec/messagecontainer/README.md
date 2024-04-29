# MessageContainer
It is a Message Container for PHP, similar in functionality **MessageBag** for Laravel. However, this is aimed for 
speed and usability, and it doesn't have any dependency. 

This class is simple: 2 classes, no dependency and nothing more. You can use in any PHP project.

[![Packagist](https://img.shields.io/packagist/v/eftec/messagecontainer.svg)](https://packagist.org/packages/eftec/MessageContainer)
[![Total Downloads](https://poser.pugx.org/eftec/messagecontainer/downloads)](https://packagist.org/packages/eftec/MessageContainer)
[![Maintenance](https://img.shields.io/maintenance/yes/2024.svg)]()
[![composer](https://img.shields.io/badge/composer-%3E1.8-blue.svg)]()
[![php](https://img.shields.io/badge/php-7.4-green.svg)]()
[![php](https://img.shields.io/badge/php-8.3-green.svg)]()
[![CocoaPods](https://img.shields.io/badge/docs-70%25-yellow.svg)]()



## What is the objective?

This library stores messages (strings) in different lockers and each locker could contain different messages with different levels (error, warning, info and success). The goal of this library:

* It stores messages depending on an "id", including the severity and message (a simple text).
* **The library does not generate an error if the value we want to read does not exist**, So we don't need to use of **isset()** in our code.  It also avoids the use of **count()** and **is_array()** in our code, this library already does it for us.
  * It returns an empty value (not null) if the message does not exist or if there is no message.
  * It returns an empty array (not null) if the list of messages does not exist
  * It returns an empty locker (not null) if the locker does not exist.
* It is possible to returns the first error or warning at the same time. In this case, if the locker stores an error and a warning, then it returns the error (it has priority).
* It is able to return:
  * all messages stored in some locker or container.
  * the first message (with or without some level)
  * the number of messages (for some level)
  * if the container of locker has error or warning.
* **It is as fast as possible**

It is an example from where we could use it, the validation of a form (this library does not validate or show values it only stores the information)

![docs/validation_example.png](docs/validation_example.png)

In this example, we have :

* one **container** (the form)
* multiples textboxes (each one is a **locker**)
*  and each textbox (our lockers) could contain one of more **messages** with different levels (in this case, success or error).

If we use plain-PHP, we could show some messages of the password:

```php
echo $container['password']['error'];
```

But what if the id password does not contain any message, or if there is no error? of if there is more than error?

So we could re-define something like that: (but it will still fail if there is more than one error)

Vanilla PHP:

```php
if (isset($container['password'])) {
    if(isset($container['password']['error'])) {
        echo $container['password']['error'];
    }
}
```

And with our library

```php
// We could show the first error (or empty if none):
echo $container->getLocker('password')->firstError(); 

// it shows all errors (or nothing if none):
foreach($container->getLocker('password')->allError() as $msg) {
    echo $msg;
} 
```

## How to use it

```php
use eftec\MessageContainer;
$container=new MessageContainer(); // we create the full lockers
$container->addItem('locker1','It is a message','warning');  // we store a message inside "id1"
$container->addItem('locker1','It is a message','error');  // we store another message inside "id1"

// And later, you can process it

$lastErrorsOrWarnings=$container->get('locker1')->allErrorOrWarning();
// it will not crash even if the locker2 does not exists.
$lastErrorsOrWarnings2=$container->get('locker2')->allErrorOrWarning();

```

## Definitions

Let's say the next example of container that shows every part of the Container.

![docs/img1.png](docs/img1.png)

We have 3 levels of spaces.

* **Container**. Usually it is unique, and it is defined by our instance of **MessageContainer**.  
The container could contain from zero to multiples lockers. Each locker is identified by a unique "id".
* **Locker**. Every time we add an item, we could create or update a new container.   
Every locker could contain from zero to many error, warning, info or success and each one could
contain from zero to many messages.
* Our **messages** or **items** are categorized in 4 levels, error, warning, info and success.  
Each level could contain one or many messages (or none)

Messages are leveled as follows

| id      | Description                                                                                                                                           | Example                                   |
|---------|-------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------|
| error   | The message is an error, and it must be solved. It is our show stopper.                                                                               | Database is down                          |
| warning | The message is a warning that maybe it could be ignored.  However, the class **MessageContainer** could allow to group Error and Warning as the same. | The registry was stored but with warnings |
| info    | The message is information. For example, to log or debug an operation.                                                                                | Log is stored                             |
| success | The message is a successful operation                                                                                                                 | Order Accepted                            |

Example #2

Example of form and MessageContainer

[examples/formexample.php](examples/formexample.php)



![docs/form1.jpg](/docs/form.jpg)


Example #3:

```php
$container=new MessageContainer();
$container->addItem('id1','some msg 1','error');
$container->addItem('id1','some msg 2','error');
$container->addItem('id1','some msg 1','warning');

$container->addItem('id2','some msg 1','info');
$container->addItem('id2','some msg 1','success');

$container->addItem('id33','some msg 1','error');
$container->addItem('id33','some msg 2','error');
$container->addItem('id33','some msg 1','success');
$container->addItem('id33','some msg 2','success');
$container->addItem('id33','some msg 2','success');

// obtaining information per locker
$msg=$container->getLocker('id1')->firstErrorOrWarning(); // returns if the locker id1 has an error or warning
$msg2=$container->getLocker('id2')->allInfo(); // returns all info store in locker id2 ["some msg1","some msg2"]
$msg3=$container->getLocker('id3')->allInfo(); // (note this locker is not defined so it returns an empty array.
$msg4=$container->getLocker('id33')->hasError(); // returns true if there is an error.
$msg5=$container->getLocker('id33')->countError(); // returns the number of errors (or zero if none).

// obtaining information globally (all lockers)
$msg7=$container->hasError(); // returns true if there is an error in any locker.
$msg8=$container->allErrorArray(true); // returns all errors and warnings presents in any locker.
```

## Adding a new message

To add a new message inside a locker, we use the method **addItem()**

```php
$container->addItem(<idlocker>,<message>,<level>,<context array>);
```

Where

* **idlocker** is the identifier of the locker to where we will store our message.
* **message** is the string of the message. 
  * The message could show variables using the syntax: **{{variablename}}**.  Example **"The value <u>{{variable}}</u> is not valid"**
  * We could show the id of the locker using the syntax **{{_idlocker}}**. Example: **"The variable <u>{{_idlocker}}</u> is empty"**
* **level** is the level of message. It could be error, warning, info and success. By default, this value is **"error"**
* **context** (optional) is an associative array used to show a message with a variable. The context is set only once per **locker**.

```php
// without context:
$container->addItem('locker1','The variable price must be higher than 200','warning');

// with context:
// The variable price must be higher than 200
$container->addItem('locker2'
                    ,'The variable {{var1}} must be higher than {{var2}}'
                    ,'warning'
                    ,['var1'=>'price','var2'=>200]);
// The variable price must be higher than 200 (not 500, the context is not updated this second time)
$container->addItem('locker2'
                    ,'The variable {{var1}} must be higher than {{var2}}'
                    ,'warning'
                    ,['var1'=>'price','var2'=>500]);
// The variable price must be higher than 200 (we use the previous context)
$container->addItem('locker2'
                    ,'The variable {{var1}} must be higher than {{var2}}'
                    ,'warning');

```

> Note: We could add one or many messages to a locker.  In the later example, the locker **locker2** stores 3 messages.
>
> Note: The message is evaluated when we call the method **addItem()**

## Getting the messages

**MessageContainer** stores a list of lockers of messages. It's aimed at convenience, so it features many methods to access 
the information in different ways. 

Messages are ranked as follows

| id      | Description                                                           | Example                                   |
|---------|-----------------------------------------------------------------------|-------------------------------------------|
| error   | The message is an error, and it must be solved. It is a show stopper. | Database is down                          |
| warning | The message is a warning that maybe it could be ignored.              | The registry was stored but with warnings |
| info    | The message is information                                            | Log is stored                             |
| success | The message is a successful operation                                 | Order Accepted                            |


Sometimes, both errors are warning are considered as equals. So the system allows reading an error or warning.

Error always has the priority, then warning, info and success.  If you want to read the first message, then it starts 
searching for errors.

You can obtain a message as an array of objects of the type **MessageLocker**, as an array of string, or as a single string 
(first message)

```php
$container->get('idfield'); // container idfield
$container->get('idfield2'); // container idfield2

if($container->hasError()) {
    // Error: we do something here.
    echo "we found ".$container->errorCount()." errors in all lockers";   
}

// using messageList
if($container->hasError()) {
    // Error: we do something here.
    echo "we found ".$container->errorcount." errors in all lockers";
    
}
```

### MessageContainer

#### Count of messages of all lockers

| Name of the field | Type | Description                                         |
|-------------------|------|-----------------------------------------------------|
| $errorCount       | int  | Get the number of errors in all lockers             |
| $warningCount     | int  | Get the number of warnings in all lockers           |
| $errorOrWarning   | int  | Get the number of errors or warnings in all lockers |
| $infoCount        | int  | Get the number of information messages.             |
| $successCount     | int  | Get the number of success messages.                 |

Example:

```php
if ($container->errorcount>0) {
    // some error
}
```



#### Obtain messages or text of all lockers

| Name               | Type   | Description                                                                                | Example of result                         |
|--------------------|--------|--------------------------------------------------------------------------------------------|-------------------------------------------|
| firstErrorText()   | method | Returns the first message of error  of all lockers                                         | "Error in field"                          |
| firstWarningText() | method | Returns the first message of warning  of all lockers                                       | "warning in field"                        |
| firstInfoText()    | method | Returns the first message of info of  all lockers                                          | "info: log"                               |
| firstSuccessText() | method | Returns the first message of success  of all lockers                                       | "Operation successful"                    |
| lastErrorText()    | method | Returns the last message of error  of all lockers                                          | "Error in field"                          |
| lastWarningText()  | method | Returns the last message of warning  of all lockers                                        | "warning in field"                        |
| lastInfoText()     | method | Returns the last message of info of  all lockers                                           | "info: log"                               |
| lastSuccessText()  | method | Returns the last message of success  of all lockers                                        | "Operation successful"                    |
| allError()         | method | Returns all errors of all lockers (as an array of objects of the type **MessageLocker**)   | **MessageLocker**[]                       |
| allWarning()       | method | Returns all warning of all  lockers (as an array of objects of the type **MessageLocker**) | **MessageLocker**[]                       |
| allInfo()          | method | Returns all info of all lockers (as an array of objects of the type **MessageLocker**)     | **MessageLocker**[]                       |
| allSuccess()       | method | Returns all success of all lockers (as an array of objects of the type **MessageLocker**)  | **MessageLocker**[]                       |
| allErrorArray()    | method | Returns all errors of all lockers (as an array of texts)                                   | ["Error in field1","Error in field2"]     |
| allWarningArray()  | method | Returns all warning of all  lockers (as an array of texts)                                 | ["Warning in field1","Warning in field2"] |
| allInfoArray()     | method | Returns all info of all lockers (as an array of texts)                                     | ["Info in field1","Info in field2"]       |
| allSuccessArray    | method | Returns all success of all lockers (as an array of texts)                                  | ["Info in field1","Info in field2"]       |

```php
echo $container->firstErrorText(); // returns first error if any
$array=$container->allError();  // MessageLocker[]
echo $array[0]->firstError(); 
$array=$container->allErrorArray();  // string[]
echo $array[0]; 
```

#### Css for a specific container

It is possible to obtain a CSS class based in the current level or state of a container.

* **$cssClasses** (field) is an associative array to use with the method cssClass()

* **cssClasses**() is method that returns a class based in the type of level of the container

```php
$css=$this-messageList->cssClasses('container1');
```

#### Misc

| Name       | Type   | Description                                                                                           |
|------------|--------|-------------------------------------------------------------------------------------------------------|
| $items     | field  | We get all lockers (array of the type **MessageLocker**). Each container could contain many messages. |
| resetAll() | method | $array=$this-messageList->items; $this-messageList->items['id'];Delete all lockers and reset counters |
| addItem()  | method | It adds a new message to a container                                                                  |
| allIds()   | method | Get all the id of the lockers                                                                         |
| get()      | method | Get a container (as an object of the type **MessageLocker**). You can also use items[]                |
| hasError() | method | Returns true if there is an error.                                                                    |

```php
echo $container->resetAll(); // resets all lockers
$container->addItem('containerid','it is a message','error'); // we add an error in the container with #id containerid
$array=$container->allIds(); // ['containerid']
var_dump($validation->get('containerid'));  // object MessageLocker

$array=$this-messageList->items;
var_dump($this-messageList->items['containerid']); // object MessageLocker

if($container->hasError()) { // $validation->hasError() does the same
    echo "there is an error";
}
```

### MessageLocker

Inside **MessageContainer** we could have one or many lockers( **MessageLocker** ).

#### Obtain messages of a specific container

| Name               | Type   | Description                                               | Example of result                         |
|--------------------|--------|-----------------------------------------------------------|-------------------------------------------|
| firstErrorText()   | method | Returns the first message of error  of a container        | "Error in field"                          |
| firstWarningText() | method | Returns the first message of warning  of a container      | "warning in field"                        |
| firstInfoText()    | method | Returns the first message of info of  a container         | "info: log"                               |
| firstSuccessText() | method | Returns the first message of success  of a container      | "Operation successful"                    |
| lastErrorText()    | method | Returns the last message of error  of a container         | "Error in field"                          |
| lastWarningText()  | method | Returns the last message of warning  of a container       | "warning in field"                        |
| lastInfoText()     | method | Returns the last message of info of  a container          | "info: log"                               |
| lastSuccessText()  | method | Returns the last message of success  of a container       | "Operation successful"                    |
| allError()         | method | Returns all errors of a container (as an array of texts)  | ["Error in field1","Error in field2"]     |
| allWarning()       | method | Returns all warning of a container (as an array of texts) | ["Warning in field1","Warning in field2"] |
| allInfo()          | method | Returns all info of a container (as an array of texts)    | ["Info in field1","Info in field2"]       |
| allSuccess()       | method | Returns all success of a container (as an array of texts) | ["Info in field1","Info in field2"]       |

```php
$container->get('idfield'); // container idfield 

echo $container->firstErrorText(); // we show the first error (if any) in the container
var_dump($container->allError); // we show the all errors
```

## Definitions of the classes

# Table of contents

<!-- TOC -->
* [MessageContainer](#messagecontainer)
  * [What is the objective?](#what-is-the-objective)
  * [How to use it](#how-to-use-it)
  * [Definitions](#definitions)
  * [Adding a new message](#adding-a-new-message)
  * [Getting the messages](#getting-the-messages)
    * [MessageContainer](#messagecontainer-1)
      * [Count of messages of all lockers](#count-of-messages-of-all-lockers)
      * [Obtain messages or text of all lockers](#obtain-messages-or-text-of-all-lockers)
      * [Css for a specific container](#css-for-a-specific-container)
      * [Misc](#misc)
    * [MessageLocker](#messagelocker)
      * [Obtain messages of a specific container](#obtain-messages-of-a-specific-container)
  * [Definitions of the classes](#definitions-of-the-classes)
* [Table of contents](#table-of-contents)
  * [MessageContainer](#messagecontainer-2)
    * [Field items (MessageLocker[])](#field-items-messagelocker)
    * [Field errorCount (int)](#field-errorcount-int)
    * [Field warningCount (int)](#field-warningcount-int)
    * [Field errorOrWarningCount (int)](#field-errororwarningcount-int)
    * [Field infoCount (int)](#field-infocount-int)
    * [Field successCount (int)](#field-successcount-int)
    * [Field cssClasses (string[])](#field-cssclasses-string)
    * [Method __construct()](#method-__construct)
    * [Method resetAll()](#method-resetall)
    * [Method addItem()](#method-additem)
      * [Parameters:](#parameters)
    * [Method allIds()](#method-allids)
    * [Method get()](#method-get)
      * [Parameters:](#parameters-1)
    * [Method getLocker()](#method-getlocker)
      * [Parameters:](#parameters-2)
    * [Method cssClass()](#method-cssclass)
      * [Parameters:](#parameters-3)
    * [Method firstErrorOrWarning()](#method-firsterrororwarning)
      * [Parameters:](#parameters-4)
    * [Method firstErrorText()](#method-firsterrortext)
      * [Parameters:](#parameters-5)
    * [Method firstWarningText()](#method-firstwarningtext)
      * [Parameters:](#parameters-6)
    * [Method firstInfoText()](#method-firstinfotext)
      * [Parameters:](#parameters-7)
    * [Method firstSuccessText()](#method-firstsuccesstext)
      * [Parameters:](#parameters-8)
    * [Method lastErrorOrWarning()](#method-lasterrororwarning)
      * [Parameters:](#parameters-9)
    * [Method lastErrorText()](#method-lasterrortext)
      * [Parameters:](#parameters-10)
    * [Method lastWarningText()](#method-lastwarningtext)
      * [Parameters:](#parameters-11)
    * [Method lastInfoText()](#method-lastinfotext)
      * [Parameters:](#parameters-12)
    * [Method lastSuccessText()](#method-lastsuccesstext)
      * [Parameters:](#parameters-13)
    * [Method allArray()](#method-allarray)
      * [Parameters:](#parameters-14)
    * [Method allErrorArray()](#method-allerrorarray)
      * [Parameters:](#parameters-15)
    * [Method allWarningArray()](#method-allwarningarray)
    * [Method allErrorOrWarningArray()](#method-allerrororwarningarray)
    * [Method allInfoArray()](#method-allinfoarray)
    * [Method AllSuccessArray()](#method-allsuccessarray)
    * [Method allAssocArray()](#method-allassocarray)
      * [Parameters:](#parameters-16)
    * [Method hasError()](#method-haserror)
      * [Parameters:](#parameters-17)
  * [MessageLocker](#messagelocker-1)
    * [Method __construct()](#method-__construct-1)
      * [Parameters:](#parameters-18)
    * [Method setContext()](#method-setcontext)
      * [Parameters:](#parameters-19)
    * [Method addError()](#method-adderror)
      * [Parameters:](#parameters-20)
    * [Method replaceCurlyVariable()](#method-replacecurlyvariable)
      * [Parameters:](#parameters-21)
    * [Method addWarning()](#method-addwarning)
      * [Parameters:](#parameters-22)
    * [Method addInfo()](#method-addinfo)
      * [Parameters:](#parameters-23)
    * [Method addSuccess()](#method-addsuccess)
      * [Parameters:](#parameters-24)
    * [Method countErrorOrWarning()](#method-counterrororwarning)
    * [Method countError()](#method-counterror)
    * [Method countWarning()](#method-countwarning)
    * [Method countInfo()](#method-countinfo)
    * [Method countSuccess()](#method-countsuccess)
    * [Method first()](#method-first)
      * [Parameters:](#parameters-25)
    * [Method firstError()](#method-firsterror)
      * [Parameters:](#parameters-26)
    * [Method firstWarning()](#method-firstwarning)
      * [Parameters:](#parameters-27)
    * [Method firstErrorOrWarning()](#method-firsterrororwarning-1)
      * [Parameters:](#parameters-28)
    * [Method firstInfo()](#method-firstinfo)
      * [Parameters:](#parameters-29)
    * [Method firstSuccess()](#method-firstsuccess)
      * [Parameters:](#parameters-30)
    * [Method last()](#method-last)
      * [Parameters:](#parameters-31)
    * [Method lastError()](#method-lasterror)
      * [Parameters:](#parameters-32)
    * [Method lastWarning()](#method-lastwarning)
      * [Parameters:](#parameters-33)
    * [Method lastErrorOrWarning()](#method-lasterrororwarning-1)
      * [Parameters:](#parameters-34)
    * [Method lastInfo()](#method-lastinfo)
      * [Parameters:](#parameters-35)
    * [Method lastSuccess()](#method-lastsuccess)
      * [Parameters:](#parameters-36)
    * [Method all()](#method-all)
      * [Parameters:](#parameters-37)
    * [Method allError()](#method-allerror)
    * [Method allWarning()](#method-allwarning)
    * [Method allErrorOrWarning()](#method-allerrororwarning)
    * [Method allInfo()](#method-allinfo)
    * [Method allSuccess()](#method-allsuccess)
    * [Method allAssocArray()](#method-allassocarray-1)
      * [Parameters:](#parameters-38)
    * [Method hasError()](#method-haserror-1)
      * [Parameters:](#parameters-39)
    * [Method throwOnError()](#method-throwonerror)
      * [Parameters:](#parameters-40)
  * [changelog](#changelog)
<!-- TOC -->

------

## MessageContainer
Class MessageList
### Field items (MessageLocker[])
Array of containers
### Field errorCount (int)
Number of errors stored globally
### Field warningCount (int)
Number of warnings stored globally
### Field errorOrWarningCount (int)
Number of errors or warning stored globally
### Field infoCount (int)
Number of information stored globally
### Field successCount (int)
Number of success stored globally
### Field cssClasses (string[])
Used to convert a type of message to a css class

### Method __construct()
MessageList constructor.

### Method resetAll()
It resets all the container and flush all the results.

### Method addItem()
You could add a message (including errors,warning..) and store it in a $idLocker
#### Parameters:
* **$idLocker** Identified of the locker (where the message will be stored) (string)
* **$message** message to show. Example: 'the value is incorrect' (string)
* **$level** =['error','warning','info','success']\[$i] (string)
* **$context** [optional] it is an associative array with the values of the item<br>
For optimization, the context is not update if exists another context. (array)

### Method allIds()
It obtains all the ids for all the lockers.

### Method get()
Alias of $this->getMessage()
#### Parameters:
* **$idLocker** ID of the locker (string)

### Method getLocker()
It returns a MessageLocker containing a locker.<br>
<b>If the locker doesn't exist then it returns an empty object (not null)</b>
#### Parameters:
* **$idLocker** ID of the locker (string)

### Method cssClass()
It returns a css class associated with the type of errors inside a locker<br>
If the locker contains more than one message, then it uses the most severe one (error,warning,etc.)<br>
The method uses the field <b>$this->cssClasses</b>, so you can change the CSS classes.
<pre>
$this->clsssClasses=['error'=>'class-red','warning'=>'class-yellow','info'=>'class-green','success'=>'class-blue'];
$css=$this->cssClass('customerId');
</pre>
#### Parameters:
* **$idLocker** ID of the locker (string)

### Method firstErrorOrWarning()
It returns the first message of error or empty if none<br>
If not, then it returns the first message of warning or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method firstErrorText()
It returns the first message of error or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value. (string)
* **$includeWarning** if true then it also includes warning but any error has priority. (bool)

### Method firstWarningText()
It returns the first message of warning or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method firstInfoText()
It returns the first message of information or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method firstSuccessText()
It returns the first message of success or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method lastErrorOrWarning()
It returns the last message of error or empty if none<br>
If not, then it returns the last message of warning or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method lastErrorText()
It returns the last message of error or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value. (string)
* **$includeWarning** if true then it also includes warning but any error has priority. (bool)

### Method lastWarningText()
It returns the last message of warning or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method lastInfoText()
It returns the last message of information or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method lastSuccessText()
It returns the last message of success or empty if none
#### Parameters:
* **$default** if not message is found, then it returns this value (string)

### Method allArray()
It returns an array with all messages of any type of all lockers
#### Parameters:
* **$level** =[null,'error','warning','errorwarning','info','success']\[$i] the level to show.<br>
Null means it shows all errors (null|string)

### Method allErrorArray()
It returns an array with all messages of error of all lockers.
#### Parameters:
* **$includeWarning** if true then it also includes warnings. (bool)

### Method allWarningArray()
It returns an array with all messages of warning of all lockers.

### Method allErrorOrWarningArray()
It returns an array with all messages of errors and warnings of all lockers.

### Method allInfoArray()
It returns an array with all messages of info of all lockers.

### Method AllSuccessArray()
It returns an array with all messages of success of all lockers.

### Method allAssocArray()
It returns an associative array of the form <br>
<pre>
[
['id'=>'', // id of the locker
'level'=>'' // level of message (error, warning, info or success)
'msg'=>'' // the message to show
]
]
</pre>
#### Parameters:
* **$level** param null|string $level (null|string)

### Method hasError()
It returns true if there is an error (or error and warning).
#### Parameters:
* **$includeWarning** If true then it also returns if there is a warning (bool)

------

## MessageLocker
Class MessageLocker

### Method __construct()
MessageLocker constructor.
#### Parameters:
* **$idLocker** param null|string $idLocker (null|string)
* **$context** param array|null $context (array|null)

### Method setContext()
We set the context only if the current context is null.
#### Parameters:
* **$context** The new context. (array|null)

### Method addError()
It adds an error to the locker.
#### Parameters:
* **$msg** The message to store (mixed)

### Method replaceCurlyVariable()
Replaces all variables defined between {{ }} by a variable inside the dictionary of values.<br>
Example:<br>
replaceCurlyVariable('hello={{var}}',['var'=>'world']) // hello=world<br>
replaceCurlyVariable('hello={{var}}',['varx'=>'world']) // hello=<br>
replaceCurlyVariable('hello={{var}}',['varx'=>'world'],true) // hello={{var}}<br>
#### Parameters:
* **$string** The input value. It could contain variables defined as {{namevar}} (string)

### Method addWarning()
It adds a warning to the locker.
#### Parameters:
* **$msg** The message to store (mixed)

### Method addInfo()
It adds an information to the locker.
#### Parameters:
* **$msg** The message to store (mixed)

### Method addSuccess()
It adds a success to the locker.
#### Parameters:
* **$msg** The message to store (mixed)

### Method countErrorOrWarning()
It returns the number of errors or warnings contained in the locker

### Method countError()
It returns the number of errors contained in the locker

### Method countWarning()
It returns the number of warnings contained in the locker

### Method countInfo()
It returns the number of infos contained in the locker

### Method countSuccess()
It returns the number of successes contained in the locker

### Method first()
It returns the first message of any kind.<br>
If error then it returns the first message of error<br>
If not, if warning then it returns the first message of warning<br>
If not, then it shows the first info message (if any)<br>
If not, then it shows the first success message (if any)<br>
If not, then it shows the default message.
#### Parameters:
* **$defaultMsg** param string $defaultMsg (string)
* **$level** =[null,'error','warning','errorwarning','info','success']\[$i] the level to show (by
default it shows the first message of any level
, starting with error) (null|string)

### Method firstError()
It returns the first message of error, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method firstWarning()
It returns the first message of warning, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method firstErrorOrWarning()
It returns the first message of error or warning (in this order), if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method firstInfo()
It returns the first message of info, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method firstSuccess()
It returns the first message of success, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method last()
It returns the last message of any kind.<br>
If error then it returns the last message of error<br>
If not, if warning then it returns the last message of warning<br>
If not, then it shows the last info message (if any)<br>
If not, then it shows the last success message (if any)<br>
If not, then it shows the default message.
#### Parameters:
* **$defaultMsg** param string $defaultMsg (string)
* **$level** =[null,'error','warning','errorwarning','info','success']\[$i] the level to show (by
  default it shows the last message of any level
  , starting with error) (null|string)

### Method lastError()
It returns the last message of error, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method lastWarning()
It returns the last message of warning, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method lastErrorOrWarning()
It returns the last message of error or warning (in this order), if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method lastInfo()
It returns the last message of info, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method lastSuccess()
It returns the last message of success, if any. Otherwise, it returns the default value
#### Parameters:
* **$default** param string $default (string)

### Method all()
Returns all messages or an empty array if none.
#### Parameters:
* **$level** =[null,'error','warning','errorwarning','info','success']\[$i] the level to show. Null
means it shows all errors (null|string)

### Method allError()
Returns all messages of errors (as an array of string), or an empty array if none.

### Method allWarning()
Returns all messages of warning, or an empty array if none.

### Method allErrorOrWarning()
Returns all messages of errors or warnings, or an empty array if none

### Method allInfo()
Returns all messages of info, or an empty array if none.

### Method allSuccess()
Returns all messages of success, or an empty array if none.

### Method allAssocArray()
It returns an associative array of the form:<br>
<pre>
[
['id'=>'', // id of the locker
'level'=>'' // level of message (error, warning, info or success)
'msg'=>'' // the message to show
]
]
</pre>
#### Parameters:
* **$level** =[null,'error','warning','errorwarning','info','success']\[$i] the level to show.
Null means it shows all messages regardless of the level (starting with error) (null|string)

### Method hasError()
It returns true if there is an error (or error and warning).
#### Parameters:
* **$includeWarning** If true then it also returns if there is a warning (bool)

### Method throwOnError()
If we store an error then we also throw a PHP exception.
#### Parameters:
* **$throwOnError** if true (default), then it throws an excepcion every time we store an error.
* **$includeWarning** If true then it also includes warnings.

------

## changelog
* 2.9 2024-03-02  * Updating dependency to PHP 7.4. The extended support of PHP 7.2 ended 3 years ago.
  * Added more type hinting in the code.
* 2.8 2023-01-28
  * **[new]** function getLog(),setLogFilename(),backupLog(),restoreLog()
* 2.7 2023-01-28
  * Now it is possible to log every message when it is an error,warning,info or success.
  * **[new]** function setLog(),log(),getLogFilename() and count()
* 2.6 2023-01-26
  * Fixed some typos.
* 2.5 2022-03-22
  * **[new]** Added type hinting to the library
  * **[fix]** Added a description to composer.json
* 2.4 2022-02-06
  * **[new]** **[container]** new methods resetLocker() and hasLocker()
  * **[new]** **[locker]** new method resetAll() 
* 2.3 2022-02-05
  * Added the right version in the documentation. No other change is done.

* 2.2 2022-02-05
  * **[new]** Now it is possible to read the last message (error, warning, info, all) in the container and in the locker
  * **[new]** MessageLocker does not store the first message anymore as a private field, it is now calculated each time.
  * **[new]** Method logOnError() that calls to error_log() when we generate an error or warning.
    * **[new]** Method ::instance() allows to get an instance of the container (singleton), if not, then it is created.
    * **[new]** Construct by default replaces the instance, however, you can set to not to replace it. It is useful if you want to have more than one instance.
* 2.1 2022-02-05
  * **[fix]** Update dependency. Now, it only works with PHP 7.2 and higher.  It is also tested for PHP 8.1
  * **[fix]** Update PHPUnit dependency.
  * **[new]** Now methods have type hinting (return values)
* 2.0.1 2022-01-29
  * [fix] some cleanups
  * [new] added method throwOnError(). So it is possible to throw an exception when we store an error and/or warning.
    * It only throws if the error or warning is throw via the container.
* 2.0 2022-01-15 
  * Dropping PHP 5.X. Now it requires PHP 7.1 or higher 
* 1.2 2021-03-21 Added new methods.
  * Optionally, messages could use variables obtained from a context. The context is per locker.  Example "it is a {{variable}}"  
* 1.1 2021-03-17 some cleanups
* 1.0 2021-03-17 first version 

