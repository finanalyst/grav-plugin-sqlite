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

### Database Installation
An adminstrator must create a directory for the database and place within it the *sqlite3* database file. It is recommended that the directory is `user/data/sqlite` (see configuration).

## Configuration

Before configuring this plugin, you should copy the `user/plugins/sqlite/sqlite.yaml` to `user/config/plugins/sqlite.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
database_route: data/sqlite
database_name: db.sqlite3
extra_security: false
logging: false
all_logging: false # this option only becomes active when logging is True
error_logging: false # this option only becomes active is False
select_logging: false
insert_logging: false
update_logging: false
```
- `enabled` turns on the plugin for the whole site. If `false`, then making it active on a page will have no effect.  
- `database_route` is the Grav route (relative to the 'user' subdirectory) to the location of the `SQLite3` database.  
- `database_name` is the full name (typically with the extension .sqlite3) of the database file. It is the responsibility of the site developer/maintainer to create the database.
- `extra_security` enables a more paranoid setting. When `true`, a page may only contain an [sql-table] shortcode if the page header explicitly allows for on. (See below for onpage configuration when option is enabled.)
- `logging` when false, nothing extra happens. When `true`, SQL related data is logged to a file called `sqlite.txt` in the directory given by `database_route`. If however there is an error in setting `database_route`,
then the directory is `user/data/sqlite`.

>SUGGESTION: If the DataManager plugin is installed and the default route is retained, then the SQL logs can be viewed from the Admin panel.

- `all_logging` only become active when `logging` is enabled. If true, then all stanzas and errors are recorded.
- `error_logging` only becomes active when `logging` is enabled and `all_logging` is not enabled.
- `select_logging` only becomes active when `logging` is enabled and `all_logging` is not enabled.
- `insert_logging` only becomes active when `logging` is enabled and `all_logging` is not enabled.
- `update_logging` only becomes active when `logging` is enabled and `all_logging` is not enabled.

>NOTE: The database must exist. If it does not, then an error is generated.    
`logging` should not be used in production settings as it writes to the hard drive, slowing performance.

### Per page configuration
* Shortcodes can be enabled separately using the `shortcode-core` configuration. To disable shortcodes being used on all pages, but only used on selected pages, configure the shortcode-core plugin inside the Admin panel with `enabled=true` and `active=false`. Then on each page where shortcodes are used, include in the front section of the page:
```yaml
shortcode-core:
  active: true
```
* When `extra_security` is enabled, then on a page in which the `[sql-table]` shortcode may be used, the page header must contain
```yaml
sqliteSelect: allow
```

## Usage
A shortcode and a Form action are provided.
1. `[sql-table]` to generate a table (or ***json*** string) from data in the database
2. the `sql-insert` action for a ***Form*** is used to move data from a form to the database.
1. the `sql-update` action for a ***Form*** is used to update an existing row of data in the database.

When the plugin is first initialised, it verifies that the database exists. If it does not exist, then every instance of the `[sql-table]` shortcode is replaced with an error message and the Form generates an error message when the submit button is pressed.

>NOTE: Although as many errors as possible are trapped, it should be remembered that GRAV uses redirects
extensively, eg., in login forms or sequential forms, which means the error messages may be overwritten. If
this happens, then set `error_logging` to **true** whilst debugging.

### [sql-table] Shortcode

In the page content the shortcode is used as follows:
```md
[sql-table]SELECT stanza[/sql-table]
```

If `extra_security` is not enabled, or `extra_security` is enabled ** AND ** the page header contains the field `sqliteSelect: allow`, then
the plugin then generates an html table (or json, see below) with the headers as returned by the select stanza, and the body containing the row data. (In the remainder of this documentation, it is assumed that `extra_security` is **NOT** enabled.)

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
1. `where` - This is mandatory for `sql-update` & ignored for `sql-insert`.
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

Some plugins allow for authorised users to modify content in the frontend. This would allow a user to add an `[sql-table]` within the markdown content of a page, and thus to access data on a website database. In order to allow a website designer to protect against such an accidental or malicious intrusion, the `extra_security` option is provided in the `sqlite` plugin configuration. It is `false` by default, to allow for backward compatibility. (See above for more information about usage.)

## Example

The following is part of a page to show how to combine `sqlite` and the `datatable` shortcode, together with jQuery code to update a database.

When the page is generated, the Form plugin creates the html of a `<form id="xxx">` where xxx is the name of the form in the header. Then the outer shortcode is called, which calls the inner shortcodes. The outer shortcode is a `[datatables]`, which initialises a DataTables object and links to the DataTables jQuery. The DataTables query  limits the number rows on the page, provides ordering and search functionality. When a row is selected, it also provides the funactionality for extracting the data from the row on a column by column basis.

The `[datatables]` shortcode expects to have an html `<table>`as its content. This is provided by the `[sql-table]` shortcode.

In order to provide for JQuery code that can be triggered by clicking on row, and so transfering data from the table to the form, a `[dt-script]` shortcode is added. When a row is first selected, the class selected is added to the row, which can then be rendered differently. When the row is clicked again, it is deselected.

When the `submit` button is clicked on the form, the data in the fields, which has been transfered from the DataTable, is  proceessed by the `sql-update` form  action, and the database is updated.

The following code would be in `form.md` file.
```
---
title: Alter User data
form:
  name: alter-client-form # this is the name given to the form and is the select for jQuery code.
  fields:
    - name: name  # this field is for displaying the selected client
      label: Client
      type: display
      content: undefined
    - name: client # this field is needed in the redirect page to report on the changes
      type: hidden
    - name: client_id # this is the PRIMARY key
      type: hidden
    - name: telephone # a field that is to be altered depending on input
      label: 'Telephone # [ eg. 1234 5678 ]'
      type: text
      validate:
        pattern: '[0-9]{4}\s[0-9]{4}'
      help: Telephone should be like: 1234 5678
      placeholder: 1234 5678
    - name: idserial
      label: 'HKID # eg. A123456(7), use capital letters'
      type: text
      validate:
        pattern: '[A-Z]{1,2}[0-9]{6}\([0-9A]\)'
      help: Should be like: A123456(7)
      placeholder: A123456(7)
    - name: dtest
      label: 'Driving Test result'
      type: text
      validate:
        pattern: '[0-2]\|.+?\|2[0-9]{3}-[0-9]{2}-[0-9]{2}'
    - name: where # this is essential for an UPDATE stanza.
      type: hidden
    - name: type
      label: Client registration type
      type: select
      options:
        Ordinary: Ordinary
        VIP: VIP
        Senior: Senior
        Support: Support
        Blacklist: Blacklist        
  buttons:
    - type: submit
      value: "Alter Client Data"
    - type: reset
      value: Reset
  process:
    - sql-update:
        table: clients # the table that is to be updated
        ignore:
            - client # we wanted the client field for the redirect, but we want to exclude it from the UPDATE stanza
    - redirect: operations/alteruser/info # at this route, there is an file with twig to display the updated info
  reset: true
cache_enable: false
---
# Clients
[datatables]
[sql-table hidden="client_id"]
SELECT client_id, name || " " || upper( surname ) as client, telephone, type, idserial as 'HKID #', dtest as 'Driver Status'
FROM clients
/* This is a simple SELECT statement. client_id is the PRIMARY key and replaces row-id.
This short code will generate a table with five columns (the client_id column is hidden) */
[/sql-table]
[dt-script]
    /* the dt-script shortcode is part of the datatables plugin. It is included as part of jQuery function
    ** that initialises the datatables jQuery plugin.
    ** It is jQuery code.
    ** The variable 'selector' is generated by the [datatables] shortcode.
    */
    var table = $(selector).DataTable();
    $(selector + ' tbody').on( 'click', 'tr', function () {
        /* the function is triggered when a row of the table is selected with a mouse click.
        ** the class selected is styled by css associated with Datatables.
        */
        if ( $(this).hasClass('selected') ) {
            // if a row is already selected when clicked, it is deselected
            // the data transfered when a row is selected is re-initialised.
            $(this).removeClass('selected');
            $('#alter-client-form input[name="data[where]"]').val('');
            /* the form name is defined in the page header
            ** GRAV Form generates input elements with the attribute
            ** name=data[xxxx] where xxxx is the name of the field in the form definition
            */
            $('#alter-client-form input[name="data[client]"]').val('');
            $('#alter-client-form input[name="data[telephone]"]').val('');
            $('#alter-client-form select[name="data[type]"]').val('Ordinary');
            $('#alter-client-form input[name="data[idserial]"]').val('');
            $('#alter-client-form input[name="data[dtest]"]').val('');
            $('#alter-client-form div:first-of-type div:nth-of-type(2) div').html('undefined');
            /* This selector refers to where the Display  field is generated in the FORM.
            */
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rd = table.row('.selected').data();
            // get a row using a function provided by DataTables
            $('#alter-client-form input[name="data[where]"]').val('client_id=' + rd[0]);
            /* this is vital for an UPDATE. It is provided as the WHERE clause.
            ** Here we get the client_id from the selected row of the table. It is in the
            ** first (zeroth) column
            */
            $('#alter-client-form input[name="data[client]"]').val(rd[1]);
            // transfer data from the DataTable to the form input elements
            $('#alter-client-form input[name="data[telephone]"]').val(rd[2]);
            $('#alter-client-form select[name="data[type]"]').val(rd[3]);
            $('#alter-client-form input[name="data[idserial]"]').val(rd[4]);
            $('#alter-client-form input[name="data[dtest]"]').val(rd[5]);
            // Transfer data to the display field to show the Name of the selected client
            $('#alter-client-form div:first-of-type div:nth-of-type(2) div').html(rd[1]);
        }
    } );
    $('#alter-client-form').on('reset', function(e) {
        setTimeout( function() {
            // a reset button is provided, so when clicked re-initialise for form
            table.$('tr.selected').removeClass('selected');
            $('#alter-client-form input[name="data[where]"]').val('');
            $('#alter-client-form input[name="data[client]"]').val('');
            $('#alter-client-form input[name="data[telephone]"]').val('');
            $('#alter-client-form input[name="data[idserial]"]').val('');
            $('#alter-client-form input[name="data[dtest]"]').val('');
            $('#alter-client-form select[name="data[type]"]').val('Ordinary');
            $('#alter-client-form div:first-of-type div:nth-of-type(2) div').html('undefined');
        });
    });
[/dt-script]
[/datatables]
```

## Credits
For bug fixes, thanks to
- Matt Marsh @marshmm
- @dlannan

## To Do
- Internationalise. Add more languages to `langages.yaml`
