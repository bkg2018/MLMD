# I) Installation<A id="a2"></A>

MLMD consists of a main PHP 7 script `mlmd.php` and a set of dependencies PHP files. The script and its
dependencies files can be put anywhere at user choice.

## I-1) PHP version<A id="a3"></A>

MLMD has been tested with PHP 7.3, 7.4 and 8.0 CLI version. Version 7.2 at least is required.

To make sure PHP is accessible from a command line type the following command:

```code
php -v
```

It should display something like the following lines (exact text may vary):

```code
PHP 7.3.20 (cli) (built: Jul  9 2020 23:50:54) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.20, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.3.20, Copyright (c) 1999-2018, by Zend Technologies
```

The directory where the PHP installation and its setting files lie can be displayed with `php --ini`.

PHP 7.2 may work but have not been tested. The Multibyte extension (mb) is needed but should not
imply a specific setting as it should be embedded in standard PHP 7.3 distributions.

## I-2) Storing MLMD<A id="a4"></A>

The PHP script and its dependencies must be put in a directory with easy user access, e.g.:

- `~/phpscripts` on macOS/Linux
- `%HOMEDRIVE%%HOMEPATH%\phpscripts` on Windows

Parameters that can be passed to the script are described in [How To Use MLMD](#how-to-use-mlmd)

## I-3) Using an alias to launch MLMD<A id="a5"></A>

This is optional and allows to type `mlmd` as if it were a command of the Operating System or
command shell. Without aliases, the script must be launched by typing `php <your_path_to_mlmd>/mlmd.php`.

The commands detailed in the following examples must be adapted to the directory where the script has
been stored.

### I-3.1) Linux / macOS / OS X<A id="a6"></A>

- The following alias command must be put in the shell startup script
(most likely `~/.bashrc`, `~/.zshrc` etc):

```code
alias mlmd=php ~/phpscripts/mlmd.php
```

### I-3.2) Windows 10<A id="a7"></A>

- A text file must be created (e.g. using NOTEPAD.EXE) containing the following line:

```code
doskey mlmd=php %HOMEDRIVE%%HOMEPATH%\phpscripts\mlmd.php $*
```

- The file can be saved as `MLMD.CMD`or `mlmd.cmd` (letters case is ignored by Windows.) on the Desktop
or any user accessible directory.
- A shortcut to this CMD file must be created (right-click on file in Explorer, then create shortcut).
- The `shell:startup` directory must be opened (by hitting the *Windows* and *R* keys together and typing
`shell:startup`).
- The shortcut must be moved from its directory to this startup directory.
- Windows must be restarted.
- From then on, the `mlmd` alias is available in any command line box as a normal command.
- This method may work with earlier versions of Windows but they have not been tested.
