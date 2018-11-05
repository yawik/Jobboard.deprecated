Jobboard
========

This YAWIK module makes YAWIK to work like a jobportal.

* The startpage contains a List of Jobs
* Default role of users is "user"
* simple controller for an about page

This module is running on:

https://jobs.yawik.org

You can use this Module as starting point to write your own Module.


Installation
------------

```
cd YAWIK/modules
git clone https://github.com/yawik/Jobboard.git
cp YAWIK/modules/Jobboard/Jobboard.module.php.dist config/autoload/Jobboard.module.php
```

make sure, that ther are no cached module files by ``rm cache/module-*.php``

http://yawik.readthedocs.io/en/latest/modules/company-registration/index.html#installation
