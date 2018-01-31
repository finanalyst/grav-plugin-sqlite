# Sqlite Plugin

The **Sqlite** Plugin is for [Grav CMS](http://github.com/getgrav/grav). The shortcode `[sql-table]` and the `sqlite` ***Form*** action are provided to interact with an ***Sqlite3*** database.

## Installation

Installing the Sqlite plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install sqlite

This will install the Sqlite plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/sqlite`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `sqlite`. You can find these files on [GitHub](https://github.com/finanalyst/grav-plugin-sqlite) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/sqlite

> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error),
[Form](https://github.com/getgrav/grav-plugin-form), [ShortcodeCore](https://github.com/getgrav/grav-plugin-shortcode-core)
and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.  
The plugin also requires that the **SQLite3** extension is available with the version of php operating on your site.  


## Configuration

Before configuring this plugin, you should copy the `user/plugins/sqlite/sqlite.yaml` to `user/config/plugins/sqlite.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
database_route: data
database_name: db.sqlite3
```
- `enabled` turns on the plugin for the whole site. If `false`, then making it active on a page will have no effect.  
- `database_route` is the Grav route (relative to the 'user' subdirectory) to the location of the `SQLite3` database.  
- `database_name` is the full name (typically with the extension .sqlite3) of the database file. It is the responsibility of the site developer/maintainer to create the database.

>NOTE: The database must exist.

### Per page configuration
Shortcodes can be enabled separately using the `shortcode-core` configuration. To disable shortcodes being used on all pages, but only used on selected pages, configure the shortcode-core plugin inside the Admin panel with `enabled=true` and `active=false`. Then on each page where shortcodes are used, include in the front section of the page:
```yaml
shortcode-core:
  active: true
```

## Usage
A shortcode and a Form action are provided.
1. `[sql-table]` to generate a table from data in the database
2. `Form` action`sqlite` is used to move data from a form to the database

When the plugin is first initialised, it verifies that the database exists. If it does not exist, then every instance of the `[sql-table]` shortcode is replaced with an error message and the Form generates an error message when the submit button is pressed.

### [sql-table] Shortcode

In the page content the shortcode is used as:
```md
[sql-table]SELECT stanza[/sql-table]
```

The plugin then generates an html table with the headers as returned by the select stanza, and the body containing the row data.

The `[sql-table]...[/sql-table]` stanza can be embedded in other shortcodes, such as  [ScrolledTableShortCodePlugin](https://github.com/finanalyst/scrolled-table-shortcode), or configured with the [Tablesorter](https://github.com/Perlkonig/grav-plugin-tablesorter) plugin.

#### Example
Assuming that:
- The default plugin configuration is not changed
- The file `<path to Grav>/user/data/db.sqlite3` exists
- The database has a table `people`, which in turn has the fields
  - `name`
  - `surname`
  - `telephone`
  - `gender`  

Then the following code and sql stanza (newlines for clarity only)
```md
[sql-table]
SELECT name, surname, telephone, gender
FROM people
LIMIT 15
[/sql-table]
```
will be rendered as (white space for example only)
```html
<table>
  <thead>
    <tr>
      <th>name</th><th>surname</th><th>telephone</th><th>gender</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>xyz</td><td>qwe</td><td>1234</td><td>Male</td>
    </tr>
    ...
  </tbody>
</table>
```
#### Options
The following options are allowed
##### class
 Provided so that a css class can be added to the table. Thus
```md
[sql-table class=SomeName]SELECT stanza[/sql-table]
```
will be rendered as
```html
<table class="SomeName">
  ...
</table>
```
##### hidden
If a list of column names is provided, then the column will not be displayed. The column names must be the same as the column names returned by the SQL stanza, eg.,
```md
[sql-table hidden="id idnum"]SELECT row-id as id, Passport as idnum, name, surname, telephone from people
[/sql-table]
```
>Caveat: the string of column names is parsed by looking for white space between column names.
Consequently, the column names for the table may not contain whitespace.

Column hiding is accomplished  by adding a `style="display: none;"` to the relevant header `<th>` elements. Consequently, the data still exists in the HTML table, and so can be scrubbed.

However, the intent of this option is to make the data available for use by JS or Jquery functions, or for updating the SQL database. In this case, a row-id is needed, but usually it is irrelevant for the user.

### Form Action sqlite
A GRAV form is created within the page as described by the GRAV documentation. However, the `process` list contains the word `sqlite`.

#### Example
In the page header, assuming the page is form.md
```yaml
form:
    name: Input data
    method: POST
    fields:
      - name: name  # the value here must be the same as a field in the database
        label: Name of person
        type: text
      - name: surname
        label: Surname of the person
        type: text
      - name: telephone
        type: text
        label: Telephone number of the person
      - name: gender
        type: select
        label: Person's gender
        options:
          male: male
          female: female
      process:
        - sqlite: # this is the crucial one
            table: people # this must match the table
      buttons:
        - type: submit
          value: Add person to database
        - type: reset
          value: Reset
      reset: true # this is advised to prevent the same data being added multiple times.
```
When the submit button is pressed, the following stanza is sent to the database:
```sql
INSERT INTO table (name, surname, telephone, gender) VALUES (...)
```

The form plugin offers considerable flexibility for validating data before being sent to the database.

>NOTE: No further validation of the data is carried out by the plugin.

## Security
Security is an issue because a `sqlite` form allows a page user to modify an existing database, and therefore corrupt it - at the very least by adding unnecessary data.

The website designer should therefore make sure that [sql-form] shortcodes are only available on Grav pages that are protected.

For example, using the `Login` plugin, only users with certain privileges or belonging to certain groups can be allowed in.

Alternatively, using the `Private` plugin, a password can be created for the page.

## To Do
- Internationalise.
- Allow for Column headings to be replaced with strings containing whitespace.
- Make a `confirm` option for the Form interface so that data is confirmed before being sent to the database.
- Create a structure for updating existing elements a database.
