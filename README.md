# Sqlite Plugin

The **Sqlite** Plugin is for [Grav CMS](http://github.com/getgrav/grav). The shortcode `[sql-table]` and the ***Form*** actions `sql-insert` and `sql-update`  are provided to interact with an ***Sqlite3*** database.

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
1. `[sql-table]` to generate a table (or ***json*** string) from data in the database
2. the `sql-insert` action for a ***Form*** is used to move data from a form to the database.
1. the `sql-update` action for a ***Form*** is used to update an existing row of data in the database.

When the plugin is first initialised, it verifies that the database exists. If it does not exist, then every instance of the `[sql-table]` shortcode is replaced with an error message and the Form generates an error message when the submit button is pressed.

### [sql-table] Shortcode

In the page content the shortcode is used as:
```md
[sql-table]SELECT stanza[/sql-table]
```

The plugin then generates an html table with the headers as returned by the select stanza, and the body containing the row data.

The SELECT stanza can be complex referring to multiple tables in the database. An SQLite3 query will return a table of rows with the same number of elements, which will fit into a simple HTML table.

Since the data would normally be updated, it is recommended that the page header contains:
```yaml
cache-enabled: false
```

The `[sql-table]...[/sql-table]` stanza can be embedded in other shortcodes, such as  [ScrolledTableShortcode](https://github.com/finanalyst/grav-scrolled-table-shortcode) plugin, or configured with the [Tablesorter](https://github.com/Perlkonig/grav-plugin-tablesorter) plugin.

#### Example
Assuming that:
- The default plugin configuration is not changed
- The file `<path to Grav>/user/data/db.sqlite3` exists
- The database has a table `people`, which in turn has the fields
  - `name`
  - `surname`
  - `telephone`
  - `gender`  

Then the following code and sql stanza (standard SQLite3 allows new lines and indentation for clarity)
```md
[sql-table]
SELECT name, surname, telephone, gender
  FROM people
  LIMIT 4
[/sql-table]
```
will be rendered something like
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
    <tr>
      <td>xyz1</td><td>qwe</td><td>1234</td><td>Male</td>
    </tr>
    <tr>
      <td>xyz2</td><td>qwe</td><td>1234</td><td>Male</td>
    </tr>
    <tr>
      <td>xyz3</td><td>qwe</td><td>1234</td><td>Male</td>
    </tr>
  </tbody>
</table>
```
It is also possible for the SELECT stanza in the xxx.md file to contain a Twig variable. For example:
```md
[sql-table]
SELECT name, surname, telephone, gender
  FROM people
  LIMIT {{ userinfo.lines }}
[/sql-table]
```
For this to work, Twig processing must be enabled, viz., in the page header there should be the line
```Yaml
process:
    twig: true
```


#### Options
The following options are allowed:
- class
- id
- hidden
- json

##### class
 Provided so that a `css` class can be added to the table. Thus
```md
[sql-table class=SomeName]SELECT stanza[/sql-table]
```
will be rendered as
```html
<table class="SomeName">
  ...
</table>
```
##### id
 Provided so that an `id` can be added to the table. Thus
```md
[sql-table id=SomeId]SELECT stanza[/sql-table]
```
will be rendered as
```html
<table id="SomeId">
  ...
</table>
```
##### hidden
If a list of column names is provided, then the column will not be displayed. The column names must be the same as the column names returned by the SQL stanza, eg.,
```md
[sql-table hidden="id idnum"]SELECT row-id as id, Passport as idnum, name, surname, telephone from people
[/sql-table]
```
>Notes:
1. Column names are matched using `\s+` or whitespace.   
This means that the column heads must be a single word (including `_`). This can be done by using `AS` to rename column names in the `SELECT` statement. Since the column headers are to be hidden, it does not matter what they look like.  
2. Shortcode parameters must be in double quotes `"..."` not single quotes `'...'`.

Column hiding is accomplished  by adding a `style="display: none;"` to the relevant `<th>` and `<td>` elements. Consequently, the data still exists in the HTML table, and so can be scrubbed or viewed by looking at the page source.

However, the intent of this option is to make the data available for use by JS or Jquery functions, or for updating the SQL database, but for it not to be immediately visible. For example, in order to update a row (a feature to be added), a row-id will be needed, but usually it is irrelevant for the user to see the row-id.

##### json

Sometimes data is required as a `json` string, eg., to include as data for other shortcodes, rather than as an **HTML** `<table>`.

For this purpose, the json option is provided:
```md
[sql-table json]SELECT stanza[/sql-table]
```

The **json** string will be an array `[]` of hash elements `{}`, one hash for each row of the table.

The keys of the hash are the names of the columns in the SELECT stanza. For example,
```md
[sql-table json]
SELECT strftime('%H:%m',time,'unixepoch','localtime') as key, latitude as lat, longitude as lng
FROM tracking
WHERE id=1
[/sql-table]
```
This assumes a table of latitude and longitude readings over time in unix seconds for units with a given id.

This will be rendered by the `sqlite-plugin` as
```md
[
{ "key": "20:10", "lat": 123.012, "lng": 22.1234},
{ "key": "20:20", "lat": 123.546, "lng": 22.112}
]
```

When this option is used, the values of the other options `class`, `id`, or `hidden` are ignored because they only have significance for an **HTML** `<table>`.

### Form Action `sql-insert`
A GRAV form is created within the page as described by the GRAV documentation. However, the `process` list contains the word `sql-insert`.

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
        - sql-insert: # this is the crucial one
            table: people # this must match the table the data is being added to
        - redirect: showdata # this is optional (see note below)
      buttons:
        - type: submit
          value: Add person to database
        - type: reset
          value: Reset
      reset: true # this is advised to prevent the same data being added multiple times.
```
When the submit button is pressed, the following stanza is sent to the database:
```sql
INSERT INTO people (name, surname, telephone, gender) VALUES (...)
```

The form plugin offers considerable flexibility for validating data before being sent to the database.

>NOTE1: No further validation of the data is carried out by the plugin.

In the example above, the process list has a redirect to another slug. This is optional. However, if the data is added correctly, it can be viewed using an `[sql-table]` shortcode with an appropriate `SELECT` stanza. The redirect action replaces the Form so that if, as recommended, the `reset` option is set to `true`, returning to the Form will set to default the fields, thus preventing inadvertent data duplication.

If there is an error (non-unique data, or incorrect fields), the `redirect` action is short-circuited.

### Form Action `sql-update`
A GRAV form is created within the page as described by the GRAV documentation. However, the `process` list contains the word `sql-update`.

#### Example
In the page header, assuming the page is form.md
```yaml
form:
    name: Input data
    method: POST
    fields:
      - name: telephone
        type: text
        label: Telephone number of the person
      - name: status
        type: select
        label: Club membership
        options:
          ordinary: Ordinary
          VIP: VIP
          Senior: Senior
      - name: where # a mandatory option (note lower case only). It is the full WHERE expression
        type: hidden # this is a where FIELD
        content: ' row-id = "3" ' # the single quotes are needed to ensure the double quotes are included
      process:
        - sql-update: # this is the crucial one
            table: people # this must match the table the data is being added to
            where: ' row-id = "3" ' # an alternative to the where field.
            # a where field takes precedence over a where parameter
        - redirect: showdata # this is optional
      buttons:
        - type: submit
          value: Update person to database
        - type: reset
          value: Reset
      reset: true # this is advised to prevent the same data being added multiple times.
```
> NOTE: It is mandatory to provide `where` data, either as a form field, or a process attribute.

When the submit button is pressed, the following stanza is sent to the database:
```sql
UPDATE people
  SET telephone = <telephone> ,
          status = <status>
  WHERE row-id = "3"
```
Here <...> is the value given in the ***Form*** for the relevant field.

It is possible to include a Twig variable in the `WHERE` data, eg.,
```Yaml
form:
    process:
      - sql-update:
          table: people
          where: ' row-id = "{{ userinfo.userid }}" '
```
Then it is possible to use another mechanism, such as the `persistent-data` plugin, to arrange for a Twig variable to contain the necessary information.

For this to work, Twig processing in headers needs to be set for the site.

## Fields for `sql-update` & `sql-insert`
The following fields are defined for these two **Form** processes:

1. `table` - This is mandatory, and is the table to which the sql stanza is applied.
1. `where` - This is mandatory for `sql-update` & optional for `sql-insert` (the string `WHERE 1` is appended by default).
1. `ignore` - This is optional for both. It is followed by an array of field names that are not included in the stanza. Eg.
```yaml
form:
    process:
        - sql-insert:
            table: people
            ignore:
                - status
```

## Security
Security is an issue because a `sql-insert` and `sql-update` form actions allows a
page user to modify an existing database, and therefore corrupt it - at the very least by adding unnecessary data.

The website designer should therefore make sure that Forms with `sql-insert` and `sql-update` actions are only available on Grav pages that are protected.

For example, using the `Login` plugin, only users with certain privileges or belonging to certain groups can be allowed in.

Alternatively, using the `Private` plugin, a password can be created for the page.

## To Do
- Internationalise. Add more languages to `langages.yaml`
- Create a `confirm` option for the Form interface so that data is confirmed before being sent to the database.
