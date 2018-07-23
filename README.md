# Nodeinfo plugin for GNU social

Plugin that presents basic instance information using the [NodeInfo standard](http://nodeinfo.diaspora.software/).

At the moment, the information is presented at the "/main/nodeinfo/2.0" endpoint.

Other tools can then scrape that information and present it in various ways. For example: [https://fediverse.network/](https://fediverse.network/)

# Instructions

1. Make sure the files are in a folder called Nodeinfo if they're not already
2. Put the folder in your /local/plugins/ directory (create the directory if it doesn't exist)
3. Tell /config.php to use it with: addPlugin('Nodeinfo');

