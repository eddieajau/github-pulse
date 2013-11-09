# Github Pulse

A command-line application for generating analitics reports on a Github repository.

## Installation

Clone this repository or download the [zip](https://github.com/eddieajau/github-pulse/archive/master.zip).

From the root folder where `composer.json` is located, install the Composer dependencies:

```bash
$ composer install
```

Copy the `/etc/config.dist.json` file to `/etc/config.json`. Edit the values as follows:

* `api.username` - Your Github user name that you use to login into Github.
* `api.password` - Your Github password.
* `api.url` - The URL for your Github Enterprise site (for example `https:github.example.com/api/v3`), otherwise `https://api.github.com` is used.
* `github.user` - The user or organisation for the repository you want to analyse (for example "joomla").
* `github.repo` - The Github repository name (for example "joomla-cms").

## Usage

```bash
$ php -f bin/pulse.php -- --help

Github Pulse 1.0.0
------------------

Usage:     php -f pulse.php -- [switches]

Switches:  -h | --help    Prints this usage information.
           --user         The name of the Github user (associated with the repository).
           --repo         The name of the Github repository.
           --username     Your Github login username.
           --password     Your Github login password.

Examples:  php -f pulse.php -h
           php -f pulse.php -- --user=foo --repo=bar
```

### Example Run

The following run will produce a table of issue analytics.

```bash
$ php bin/pulse.php
[2013-11-09 08:58:56] Pulse.DEBUG: Analysing issues between 2012-11-01T00:00:00Z and 2013-11-08T22:58:56Z [] []
[2013-11-09 08:58:56] Pulse.INFO: Getting open issues page #01. [] []
[2013-11-09 08:58:56] Pulse.INFO: ---------------------------------------- [] []
[2013-11-09 08:59:00] Pulse.INFO: Got 100 issues. [] []
[2013-11-09 08:59:00] Pulse.INFO: Getting open issues page #02. [] []
[2013-11-09 08:59:00] Pulse.INFO: ---------------------------------------- [] []
<snip/>
[2013-11-09 09:00:24] Pulse.INFO: Getting closed issues page #19. [] []
[2013-11-09 09:00:24] Pulse.INFO: ---------------------------------------- [] []
[2013-11-09 09:00:25] Pulse.INFO: Got 0 issues. [] []
[2013-11-09 09:00:26] Pulse.DEBUG: Open issues: 15 [] []
## Repository Issues (Pulls)

| Date | Carried Forward | New | Closed | Left Open | Avg. Days |
| --- |:---:| --- | --- | --- | --- |
| 2013-11 | 11 (406) | 8 (63) | 4   (54) | 15 (415) | 3.3 (7.0) |
| 2013-10 | 57 (530) | 14 (273) | 60   (397) | 11 (406) | 13.3 (9.7) |
| 2013-09 | 52 (483) | 10 (203) | 5   (156) | 57 (530) | 0.7 (6.5) |
| 2013-08 | 38 (442) | 20 (237) | 6   (196) | 52 (483) | 3.6 (10.2) |
| 2013-07 | 31 (324) | 8 (230) | 1   (112) | 38 (442) | 0.6 (6.5) |
| 2013-06 | 21 (287) | 12 (175) | 2   (138) | 31 (324) | 1.5 (8.2) |
| 2013-05 | 49 (253) | 13 (107) | 41   (73) | 21 (287) | 14.7 (8.1) |
| 2013-04 | 43 (216) | 9 (141) | 3   (104) | 49 (253) | 3.5 (6.4) |
| 2013-03 | 42 (221) | 11 (176) | 10   (181) | 43 (216) | 12.5 (8.0) |
| 2013-02 | 41 (198) | 3 (40) | 2   (17) | 42 (221) | 1.5 (11.9) |
| 2013-01 | 34 (174) | 11 (63) | 4   (39) | 41 (198) | 3.5 (7.7) |
| 2012-12 | 39 (184) | 8 (37) | 13   (47) | 34 (174) | 13.1 (11.3) |
| 2012-11 | 42 (156) | 5 (51) | 8   (23) | 39 (184) | 14.1 (7.3) |

Avg. Days is the average number of days taken to close the issues or pull requests at the time they were closed.

```

It's then just a matter of copying the last part of the report and posting it in a Github wiki page.

A full run will save the data collected from the Github site in `/etc/openIssues.json` and `/etc/closedIssues.json`. You can re-run the application using this local data using the `--debug` switch (this is useful for tuning the output of the report or customising the application itself).

```bash
$ php bin/pulse.php -- --debug
[2013-11-09 09:14:09] Pulse.DEBUG: Analysing issues between 2012-11-01T00:00:00Z and 2013-11-08T23:14:09Z [] []
[2013-11-09 09:14:09] Pulse.DEBUG: 430 open issues. [] []
[2013-11-09 09:14:09] Pulse.DEBUG: 1711 closed issues. [] []
[2013-11-09 09:14:09] Pulse.DEBUG: Open issues: 15 [] []
## Repository Issues (Pulls)

| Date | Carried Forward | New | Closed | Left Open | Avg. Days |
| --- |:---:| --- | --- | --- | --- |
| 2013-11 | 11 (406) | 8 (63) | 4   (54) | 15 (415) | 3.3 (7.0) |
| 2013-10 | 57 (530) | 14 (273) | 60   (397) | 11 (406) | 13.3 (9.7) |
| 2013-09 | 52 (483) | 10 (203) | 5   (156) | 57 (530) | 0.7 (6.5) |
| 2013-08 | 38 (442) | 20 (237) | 6   (196) | 52 (483) | 3.6 (10.2) |
| 2013-07 | 31 (324) | 8 (230) | 1   (112) | 38 (442) | 0.6 (6.5) |
| 2013-06 | 21 (287) | 12 (175) | 2   (138) | 31 (324) | 1.5 (8.2) |
| 2013-05 | 49 (253) | 13 (107) | 41   (73) | 21 (287) | 14.7 (8.1) |
| 2013-04 | 43 (216) | 9 (141) | 3   (104) | 49 (253) | 3.5 (6.4) |
| 2013-03 | 42 (221) | 11 (176) | 10   (181) | 43 (216) | 12.5 (8.0) |
| 2013-02 | 41 (198) | 3 (40) | 2   (17) | 42 (221) | 1.5 (11.9) |
| 2013-01 | 34 (174) | 11 (63) | 4   (39) | 41 (198) | 3.5 (7.7) |
| 2012-12 | 39 (184) | 8 (37) | 13   (47) | 34 (174) | 13.1 (11.3) |
| 2012-11 | 42 (156) | 5 (51) | 8   (23) | 39 (184) | 14.1 (7.3) |

Avg. Days is the average number of days taken to close the issues or pull requests at the time they were closed.
```