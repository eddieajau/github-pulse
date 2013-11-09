{{%FILTERS}}
## Repository Issues (Pulls)

| Date | Carried Forward | New | Closed | Left Open | Avg. Days |
| --- |:---:| --- | --- | --- | --- |
{{#issues}}
| {{date}} | {{carriedIssues}} ({{carriedPulls}}) | {{newIssues}} ({{newPulls}}) | {{closedIssues.count}}   ({{closedPulls.count}}) | {{openIssues}} ({{openPulls}}) | {{closedIssues.mean | number.1f }} ({{closedPulls.mean | number.1f }}) |
{{/issues}}

Avg. Days is the average number of days taken to close the issues or pull requests at the time they were closed.
