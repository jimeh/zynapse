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

## Some Noteworthy Features

* A generator script which works just like Rails' `script/generate` command.
* Locale system, similar to the i18n functionality which was included in Rails 2.2, only Zynapse had it 2 years earlier.
* Preference storage system, which works much like a key/value store for application preferences, and writes data to files on disk.
* Other smaller neat features and quirks which I don't really remember anymore.

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