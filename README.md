# dokuwiki-plugin-station-termine

Produces a nice calendar based on event information stored in a struct database. It is not customizable. Instead, copy the plugin and change its implementation to fit your own needs.

Needs the struct plugin: https://www.dokuwiki.org/plugin:struct The database schema can be found in the file termin.struct.json

See it in action: http://www.station-weisswasser.de/termine:start


## Syntax 

The syntax is the same as the struct plugin's aggregation tables. See https://www.dokuwiki.org/plugin:struct:aggregation and https://www.dokuwiki.org/plugin:struct:filters. Columns do not need to be selected because the plugin selects them by name on its own. This is not particularly flexible but simplifies the code alot.

```
---- struct cal ----
schema: termin
----
```

Filtering works as usual:

```
---- struct cal ----
schema: termin
filter: anmeldung = Ja
limit: 5
----
```

