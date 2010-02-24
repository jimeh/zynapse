# Zynapse 2 (Rewrite)

This is a started and (so far) never completed attempt to rewrite Zynapse from the ground up with a completely new boot sequence, object structure, and almost everything else changed and improved as well.

One of the basic ideas was to separate the basic components better into different classes, and making sure that wether you're in a Controller, Model, or View file, you can access all the basic parts via the $this variable. Those basic parts includes the current controller, ActionBase instance, logger, session, and more.

## Initial Development Notes

What follows are some notes I made when I started working on this rewrite. Not much of this is implemented as of now, and not sure it ever will be.

* GOALS
    * More lightweight than v1.
    * Everything (including boot process) moved to classes which are initialized.
    * Serialize all but the current request process objects and store them in session or on disk for a faster boot process on each page request.
* CLASSES
    * Zynapse – The main class which loads, caches, and controls all other classes.
    * "Action" classes — Core internal workings of Zynapse
        * ActionEnvironment — "env" in code  
          Loads settings and configures all environment related settings.
        * ActionBase — "base" in code  
          Handles actual page requests, initializes the controllers, and sends the output to ActionView.
        * ActionView — "view" in code  
          Gets data output from the Controller and renders it with the corresponding view file.
        * ActionShell — "shell" in code  
          The class that initializes and runs shell scripts.
    * "Active" classes — Data storage classes of one type or another.
        * Database Storage
            * ActiveRecord  
              Object relational mapper for MySQL, SQLite, MSSQL, PostgresSQL...
            * ActiveCouch  
              CouchDB datastore to be implemented in the future.
            * ActiveMongo  
              MongoDB datastore to be implemented in the future.
        * Misc. Storage
            * ActiveLog — "log" in code  
              Logging system used by ActionBase, ActiveRecord, and other components to log what is happening.
            * ActiveSession — "session" in code  
              Session initialization and misc proceedures.
            * ActiveLocale — "locale" in code  
              Handles localization, and creates global helpers for getting and setting locale strings. Uses ActiveProperty as it's storage system.
            * ActivePreferences — "prefs" in code  
              Preference storage system. Creates global helpers for getting and setting locale strings. Uses ActiveProperty as it's storage system.
            * ActiveCache
            * AcitveProperty  
              Simple and fast "flat file" storage system. Stores any type of PHP variable by writing an actual PHP class with the values defined, which means there's no overhead in parsing the file when it's read in again.
            * ActivePropertyList  
              The class which contains the actual data of the ActiveProperty system. The ActiveProperty class simply organizes and reads in children of ActivePropertyList.
* HELPERS  
  Helpers are normal PHP functions which are available on a global scope. Some are intended for global use (like the locale helpers), and some are intended solely for use within view files.
    * Global Helpers
    * View Helpers

## License

(The MIT License)

Copyright (c) 2010 Jim Myhrberg.

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.