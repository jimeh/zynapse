# Zynapse Framework (1.x)

## Overview

A Rails-like MVC framework written in PHP5, which I never fully completed. As such, I've made it available to the public for educational reasons, and maybe someone might be interested in further developing it (the `dev` branch in that case, please!).

## History

I started building Zynapse in January 2007 after really liking the MVC structure of Ruby On Rails, but not finding any existing PHP frameworks fully fitting what I wanted. A lot of the internals are inspired by [PHPonTrax][trax], as it was the closest to what I was looking for at the time, but it still didn't feel right.

I learned an awful lot while building Zynapse, for example, I had never used SQL until I started building my own custom ActiveRecord implementation. I really was jumping into the pool at the deep end, not only was I learning how SQL worked, but I had to learn about Object-Relational Mappers, and build one complete with Rails-like associations and all.

## Current State

I've been trying to fool myself for a long time that my sweet little Zynapse project is not dead. But the fact remains that since late 2008, I've been working with Ruby On Rails, and it's for better or worse become my framework of choice now, even though I still try to deny it sometimes.

After early 2008 I hardly spent any time working on Zynapse sadly, as I simply didn't have time, and had somewhat lost interest.

While on holiday in August 2009 however, I started working on a complete rewrite of Zynapse from the ground up which I called Zynapse2. Said rewrite is available in the `dev` branch. It's highly incomplete, but if you're curious, I encourage you to fork the project and play with both the 1.x code and the rewrite in the `dev` branch.

## Requirements

* PHP 5.2.2 or later (might work on earlier 5.x, but its not tested).
* MySQL 4 or newer (untested on earlier versions).
* Apache's mod_rewrite with the `.htaccess` file enabled or similar functionality.

## Running / Testing

To get Zynapse up and running, you'll need to configure a virtual host in Apache, and make sure `mod_rewrite` is enabled, and `AllowOverride` is set to `all` so the `.htaccess` file works.

If you're not using Apache, you'll need to duplicate the `mod_rewrite` functionality and it's configuration from the `.htaccess` file for things to work.

As a last step, run `script/fix_permissions` to ensure logs, caches and other required paths are writable by Zynapse.

## Some Noteworthy Features

* A generator script which works just like Rails' `script/generate` command. Run without any arguments to see the help info.
* Locale system, similar to the i18n functionality which was included in Rails 2.2, only Zynapse had it 2 years earlier.
* Preference storage system, which works much like a key/value store for application preferences, and writes data to files on disk.
* Some neat security features which attempts to reset session data it believes a session has been hijacked.
* Can auto-detect which environment settings to run with based on the domain name used to access the app.
* Other smaller neat features and quirks which I don't really remember anymore.

## Sites Currently Powered By Zynapse

As of 25th Feb, 2010.

* [Lib.rario.us](http://lib.rario.us/) by [sxtxixtxcxh](http://github.com/sxtxixtxcxh/).
* [If X Then Y](http://ifxtheny.com/) by [sxtxixtxcxh](http://github.com/sxtxixtxcxh/).
* [Steve Jobs Facts](http://www.stevejobsfacts.com/) by [jimeh](http://github.com/jimeh) and [sxtxixtxcxh](http://github.com/sxtxixtxcxh/).
* [Eimai Malakas](http://eimaimalakas.com/) by [jimeh](http://github.com/jimeh) and [jonromero](http://github.com/jonromero).
* [Search The Three](http://www.searchthethree.com/) by [jimeh](http://github.com/jimeh) (super-old and abandoned).

## License

(The MIT License)

Copyright (c) 2009 Jim Myhrberg.

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



[trax]: http://www.phpontrax.com/