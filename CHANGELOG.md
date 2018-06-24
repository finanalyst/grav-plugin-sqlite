# v1.4.0
## 23 June 2018
1. [](#enhancement)
    * More logging options, allowing for the SELECT, INSERT and UPDATE stanzas (in addition to Errors)
    to be trapped.
    * Append the logged data to the directory `user/data/sqlite` as a 'log.txt' file
        * This allows an Administrator to view the data from within the Admin panel using the DataManager plugin.
2. [](#change of configuration)
    * The default configuration for the placement of the sqlite3 database is now `user/data/sqlite`

# v1.3.0
## 11 June 2018
1. [](#enhancement)
    * Add `ignore` field in `sql-insert` and `sql-update`
    * Add `error_logging` configuration option, so that SQL errors are written to hard drive

# v1.2.3
## 7 June 2018
1. [](#update)
    * Add shortcode dependency to blueprints

# v1.2.2
##  5/05/2018
1. [*](#bug and enhancement)
    * Process `sqlite-insert` corrected to `sql-insert`
    * Removed fields provided by `Form` plugin, viz. `_xxx` & `form-nonce`

# v1.2.1
##  5 May 2018
1. [*](#minor)
        * error in translate call

#v1.2.0
##  28 April 2018
1. [*](#major )
    * Allow for Twig variables to be used in SELECT stanza in shortcode, and in 'where' field of UPDATE Form process.

# v1.0.0 - v1.2.0
##  < 28 April 2018

1. [*](#new)
    * Initial work
2. [*](#minor)
    * Removal of debug code/messages
    * refactor in case of zero data in form.
3. [*](#minor)
    * Allow for `where` to be a Form Field as well as a Process parameters
4. [*](#major)
    * New sql option to provide json serialisation instead of HTML serialisation of data
