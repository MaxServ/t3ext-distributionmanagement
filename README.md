# TYPO3 extension: distributionmanagement
Manage TYPO3 CMS distributions from the cli using an extbase command controller

Use with [https://github.com/helhum/typo3_console](https://github.com/helhum/typo3_console) for optimum pleasure.

Clone it
```bash
git clone https://github.com/MaxServ/t3ext-distributionmanagement.git distributionmanagement
```

Or install it using composer:
```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/MaxServ/t3ext-distributionmanagement.git"
        }
    ],
    "require": {
        "maxserv/distributionmanagement": "*"
    }
}
```

Example:
```bash
$ ./typo3cms distribution:list
```

Available commands:

```bash
EXTENSION "DISTRIBUTIONMANAGEMENT":
-------------------------------------------------------------------------------
  distribution:list                        List distributions
  distribution:install                     Install distribution
```
