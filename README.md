# SkyLink for Magento 2

This document is a technical guide to the server setup, installation and maintenance of SkyLink for Magento 2. It is designed to be used in conjunction with Magento's [Installation Guide](http://devdocs.magento.com/guides/v2.0/install-gde/install-quick-ref.html).

Retail Express' innovative approach to syncing data in realtime with Magento 2 requires a little more work than a standard Magento 2 website in a technical sense.

## 1. Concepts

There are a number of concepts to familiarise yourself with in order to successfully install and maintain SkyLink for Magento 2.

### 1.1. Commands

We use the **[Command Pattern](https://en.wikipedia.org/wiki/Command_pattern)** to manage actions within SkyLink (such as syncing data). A **Command** is created and sent to a [Command Bus](https://tactician.thephpleague.com). The Command Bus then passes the command to an appropriate **Handler**, which actually does the work:

```bash
               SyncCustomerCommand                # Sync Customer Command is created
                        ↓                         # Passed through to the Command Bus
    CommandBus->handle(SyncCustomerCommand)
                        ↓                         # The Command Bus finds a Handler for the Command
SyncCustomerHandler->handle(SyncCustomerCommand)  # The Handler actually syncs the Customer
```

All syncing done by SkyLink is done through the Command Pattern. It provides a good separation of concerns and describes clearly what functionality is readily available.

Commands are PHP objects and therefore can be invoked a number of ways. Currently, invoke Commands through:

1. Magento's CLI (`bin/magento`).
2. Web requests.
3. EDS (discussed below).

### 1.2. Queued Commands

Not all of our Commands are Handled immediately. In fact, by default most of them are **Queued Commands**. Queued Commands are received by the Command Bus, but instead of finding a Handler immediately, it stores them so they can be Handled at a later time. A **Queue Worker** has the responsibility of picking out all Queued Commands from storage and passing them to the

```bash
               SyncCustomerCommand
                        ↓
    CommandBus->handle(SyncCustomerCommand)
                        x                         # Command Bus notices the Command should be Queued
             -----------------------

                    Database                      # The command is stored in the database indefinitely

             -----------------------
                        x                         # A Queue Worker picks the Command and sends
    CommandBus->handle(SyncCustomerCommand)       # it back to the Command Bus to be Handled
                        ↓
SyncCustomerHandler->handle(SyncCustomerCommand)
```

There are a number of advantages of Queued Commands (where used appropriately):

1. Handling a Command can take time. If a Command is created through a web request, it provides bad user experience to need to wait for the completion of the Handler before finishing the web request.
2. Having a long-running Queue Worker process that Handles multiple Commands is typically faster than separate processes (e.g. web requests) due to how PHP code is compiled every time a script is run.
3. You have the ability to **horizontally scale your app** by introducing more Queue Workers.

### 1.3. EDS

Event-Driven Synchronisation **(EDS)** is the way that Retail Express advises a consumer of it's API that something has changed and a sync should occur. This is the most effective way of syncing data quickly and when it is most relevant.

Traditionally, integrations will sync all data on a [cron schedule](https://en.wikipedia.org/wiki/Cron). This is resource-intensive and typically occurs overnight and not during peak traffic periods. This, of course, is not realtime syncing. Another approach is syncing on-demand when a page is loaded (such as a product page). This provides extremely accurate data, however it provides a terrible user experience as the user needs to wait for the sync to occur before the page appears. This also effectively blocks the use of advanced page caching and also leaves a website open to potential DDOS.

EDS aims to solve this by syncing data as soon as possible after it has changed in Retail Express, and only then. EDS performs two tasks:

1. EDS sends a **Change Set** to a URL (configured in Retail Express) describing what entities have changed.
2. EDS is notified when those entities have been processed.

EDS is not tied to Magento, and Magento does not *need* to use EDS.

The example below is what occurs when a Customer is saved in Retail Express:

```bash
                 EDS Change Set                   # A Change Set is created with the Customer ID
                        ↓                         # The Change Set is sent to Magento 2
              SyncCustomerCommand                 # SkyLink creates a Command to sync the Customer
                        ↓
    CommandBus->handle(SyncCustomerCommand)
                        ↓                         # SkyLink listens for the Command to be Handled
            EDS Change Set Processed              # SkyLink notifies Retail Express
```

## 2. Installing / Updating

### 2.1. System Requirements

In addition to the Magento [system requirements](http://devdocs.magento.com/guides/v2.1/install-gde/system-requirements2.html), there are further system requirements required to use all the features of SkyLink for Magento 2.

#### 2.1.1. PHP SOAP Extension

SkyLink requires you install the [SOAP](http://php.net/manual/en/book.soap.php) extension for PHP (to communicate with Retail Express' API). This can be done at compile time or as an extension. In Ubuntu 16.04, this is [done through](http://askubuntu.com/a/803007):

```bash
sudo apt-get install php7.0-soap
```

#### 2.1.2. Supervisor (Or Equivalent) *[Recommended, But Optional]*

[Supervisor](http://supervisord.org) (or [Circus](http://circus.readthedocs.io)) are tools designed to manage processes. These should be used to ensure Queue Workers *(Section 1.2)* are running at all times. There are [many ways](http://supervisord.org/installing.html) to install Supervisor. It can be installed in Ubuntu 16.04 through:

```bash
sudo apt-get install supervisor
```

### 2.2. Authentication

Retail Express provide a [Composer Repository](http://repo.ecom.retailexpress.com.au) to access extensions. **You will need to provide us with a list of your Magento development/production machines' ssh public keys prior to installing SkyLink.** These are used for authenticating against the git repositories where the code is stored.

There is loads of help on [generating ssh keys](https://help.github.com/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent/). On Ubuntu 16.04, this would be done through:

```bash
# Logged in as the Magento filesystem owner...
ssh-keygen -t rsa -b 4096 -C "your_email@example.com" # Work through prompts to generate ssh keys
```

You must then provide Retail Express with your ssh public key. In a typical installation, this is located at `~/.ssh/id_rsa.pub`. Retail Express will authorise your public key and you can continue with the installation of SkyLink for Magento 2.

### 2.3. Installing

#### 2.3.1. Add Composer Repository

Once you have authenticated your machine with Retail Express, you must add our Composer Repository to your existing Magento installation. This is done thorugh:

```bash
# Logged in as the Magento filesystem owner...
composer config repositories.retail-express composer https://repo.ecom.retailexpress.com.au
```

Alternatively, you may merge the following into your existing `composer.json` file:

```json
// If your "repositories" attribute is an array...
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://repo.ecom.retailexpress.com.au"
    }
  ]
}

// If your "repositories" attribute is already an object...
{
  "repositories": {
    "retail-express": {
      "type": "composer",
      "url": "https://repo.ecom.retailexpress.com.au"
    }
  }
}
```

#### 2.3.2. Require Composer Package

Once you have added the Composer Repository, you need to require the SkyLink for Magento 2 Composer Package:

```bash
# Logged in as the Magento filesystem owner...
composer require retail-express/skylink-magento-2
```

Alternatively (or for more granular versioning control), you may merge the following into your `composer.json` file:

```json
// Recommended...
{
	"require": {
		"retail-express/skylink-magento-2": "^1.4"
	}
}

// For beta releases (not recommended in production)...
{
	"require": {
		"retail-express/skylink-magento-2": "^1.4"
	},
	"minimum-stabiliy": "beta"
}

// For bleeding edge of all code (not recommended)...
{
	"require": {
		"retail-express/skylink-magento-2": "^1.4"
	},
	"minimum-stabiliy": "dev"
}
```

#### 2.3.3. Temporary Logging Workaround

Due to the way Magento [sets up it's logging](https://github.com/magento/magento2/issues/2529) and that it [explicitly requires an old version of Monolog (the logging tool)](https://github.com/magento/magento2/blob/2.1/composer.json#L40), an extra step is required to make SkyLink logging (or any third party logging) work properly. This is a [known issue](https://github.com/magento/magento2/issues/2529) that Magento have not resolved.

To do this, merge the following into your `composer.json` file:

```json
{
    "require": {
        "monolog/monolog": "1.18.0 as 1.16.0"
    }
}
```

Then run:

```bash
# Logged in as the Magento filesystem owner...
composer update monolog/monolog
```

#### 2.3.4. Enable Extension

After the extension's code is installed, you need to prepare it for use in Magento by first enabling it:

```bash
# Logged in as the Magento filesystem owner...
bin/magento module:enable RetailExpress_CommandBus
bin/magento module:enable RetailExpress_SkyLink
```

You may skip over *Section 2.4* and **prepare the extension** *(Section 2.5)* for Magento.

### 2.4. Updating

**To update SkyLink for Magento 2's code, you must first stop any Queue Workers *(Section 3.3.1)*.** Then, you simply run:

```bash
# Logged in as the Magento filesystem owner...
composer update retail-express/skylink-magento-2 --with-dependencies
```

Every time your code is updated, you need to **prepare the extension** *(Section 2.5)* for Magento.

### 2.5. Prepare Extension

To prepare the extension for Magento, you need to run the installation scripts and also reindex data and flush caches:

```bash
# Logged in as the Magento filesystem owner...
bin/magento setup:upgrade
bin/magento indexer:reindex
bin/magento cache:flush
```

A **strongly recommended** performance improvement is to enable caching and put Magento into production mode:

```bash
# Logged in as the Magento filesystem owner...
bin/magento cache:enable
bin/magento deploy:mode:set production -s
bin/magento setup:di:compile
bin/magento setup:static-content:deploy en_AU en_US # Add any additional locales (e.g. en_AU en_NZ) separated by a comma
bin/magento cache:clean
```

### 2.6 Install / Upgrade Checklist

1. I have checked the system requirements *(Section 2.1)*.
2. I have authenticated my development, staging and production machines *(Section 2.2)*.
3. I have installed/updated the code and the extnesion is enabled *(Sections 2.3 and 2.4)*.
4. I have prepared the extension and enabled both caching and production mode *(Section 2.5)*.

## 3. Syncing Data

There are a number of ways to sync data using SkyLink for Magento 2. Depending on your server environment, different approaches are recommended.

### 3.1. Bulk Sync

While EDS is extremely useful at syncing data at the right time for an established Magento 2 store, it is not the best way to initially sync all of your data over to Magento.

For this, we provide a number of commands in Magento's CLI:

```bash
# Logged in as the Magento filesystem owner...

# All of the commands below by default are Queued Commands,
# however, by appending the `--inline` option, you can
# make the actual sync occur in the same process
# rather than pushing the work onto the Queue.
# See Section 3.3 for more information.

# Syncs Customers from Retail Express
bin/magento retail-express:skylink:bulk-customers
bin/magento retail-express:skylink:bulk-customers --inline

# Syncs Attributes from Retail Express
bin/magento retail-express:skylink:bulk-attributes
bin/magento retail-express:skylink:bulk-attributes --inline

# Syncs Price Groups from Retail Express to Magento Customer Groups
bin/magento retail-express:skylink:bulk-price-groups
bin/magento retail-express:skylink:bulk-price-groups --inline

# Syncs Products from Retail Express
bin/magento retail-express:skylink:bulk-products
bin/magento retail-express:skylink:bulk-products --inline

# Syncs Fulfillments from Retail Express to active Magento Orders
bin/magento retail-express:skylink:bulk-fulfillments
bin/magento retail-express:skylink:bulk-fulfillments --inline
```

> Note, each of these commands has one or more arguments and options. Simply prepend `--help` to any of the aforementioned commands to see what arguments and options are available.

### 3.2. Individual Sync

It is possible to manually sync data on an individual-entity level. Typically, this is useful for debugging or development purposes. We also provide a number of commands in Magento's CLI for this:

```bash
# Logged in as the Magento filesystem owner...

# All of the commands below by default are regular Commands,
# however, by appending the `--queue` option, you can
# make them Queued Commands. See Section 3.3 for
# more information on Queue Workers.

# Syncs a customer from Retail Express
bin/magento retail-express:skylink:sync-customer :id
bin/magento retail-express:skylink:sync-customer :id --queue

# Syncs an attribute from Retail Express
bin/magento retail-express:skylink:sync-attribute :code
bin/magento retail-express:skylink:sync-attribute :code --queue

# Syncs a price group from Retail Express to a Magento Customer Group
bin/magento retail-express:skylink:sync-price-group :id
bin/magento retail-express:skylink:sync-price-group :id --queue

# Syncs a product from Retail Express
bin/magento retail-express:skylink:sync-product :id
bin/magento retail-express:skylink:sync-product :id --queue

# Syncs new Retail Express Fulfillments to Magento Shipments for the given Magento Order
bin/magento retail-express:skylink:sync-fulfillments :id
bin/magento retail-express:skylink:sync-fulfillments :id --queue
```

> Note, each of these commands has one or more arguments and options. Simply prepend `--help` to any of the aforementioned commands to see what arguments and options are available.

### 3.3. Queue Workers

It is required that you configure queue workers for the extension. These workers can be managed by a **process manager** *(Section 2.1.2)*, run on a **cron schedule** or ran manually.

If possible, it is recommended to use a process manager as this continually synchronises data. This guide assumes the use of [Supervisor](http://supervisord.org) as the chosen process manager, although concepts can easily be introduced into alternative process managers.

We provide a single Magento CLI command to, as a Queue Worker, consume Queued Commands:

```bash
bin/magento retail-express:command-bus:consume
```

There are a number of options available for this Magento CLI command, which make it perfect to configure for use in a process manager or cron schedule:

```bash
Usage:
 retail-express:command-bus:consume-queue [--max-runtime[="..."]] \
                                          [--max-messages[="..."]] \
                                          [--max-memory[="..."]] \
                                          [--priority] \
                                          [--stop-when-empty] \
                                          [--stop-on-error] \
                                          queue1 ... [queueN]

Arguments:
 queue                 # Names of one or more queues that will be consumed.

Options:
 --max-runtime         # Maximum time in seconds the consumer will run.
 --max-messages        # Maximum number of messages that should be consumed.
 --max-memory          # Maximum memory in megabytes the consumer will consume before cleanly exiting.
 --pause-when-empty    # Seconds to pause when the queue is empty. (Default 3)
 --priority            # Flag to use priority queues rather than round robin queues when consuming multiple queues.
 --stop-when-empty     # Stop consumer when queue is empty.
 --stop-on-error       # Stop consumer when an error occurs.
 --help (-h)           # Display this help message
 --quiet (-q)          # Do not output any message
 --verbose (-v|vv|vvv) # Increase the verbosity of messages: 1 for normal
                       # output, 2 for more verbose output and 3 for debug
 --version (-V)        # Display this application version
 --ansi                # Force ANSI output
 --no-ansi             # Disable ANSI output
 --no-interaction (-n) # Do not ask any interactive question
```

#### 3.3.1. Supervisor

Once Supervisor has been installed in your system, you may [configure it](http://supervisord.org/configuration.html) to sustain Queue Worker proceses. The following Supervisor config file serves as a template that should work with your installation:

```ini
[group:<magento file system owner>]
programs=<magento file system owner>_customers,<magento file system owner>_products
priority=1

[program:<magento file system owner>_customers]
user=<magento file system owner>
command=<path to php binary> <magento install dir>/bin/magento retail-express:command-bus:consume --max-memory=512 --max-runtime=300 --priority customers payments fulfillments
autorestart=true
numprocs=<number of processes>
process_name=%(program_name)s_%(process_num)s
stdout_logfile=/home/<magento file system owner>/logs/supervisor/customers.log
stderr_logfile=/home/<magento file system owner>/logs/supervisor/customers.err.log

[program:<magento file system owner>_products]
user=<magento file system owner>
command=<path to php binary> <magento install dir>/bin/magento retail-express:command-bus:consume --max-memory=512 --max-runtime=300 --priority attributes price-groups products
autorestart=true
numprocs=<number of processes>
process_name=%(program_name)s_%(process_num)s
stdout_logfile=/home/<magento file system owner>/logs/supervisor/products.log
stderr_logfile=/home/<magento file system owner>/logs/supervisor/products.err.log
```

There are three replacements that need to be made to this template:

1. `<magento file system owner>` - this is the user that owns the Magento filesystem.
2. `<path to php binary>` - this typically can be found by typing `which php` on your CLI.
3. `<number of processes>` - must be at least 1 - a value of 2 is recommended. It can be set higher however this will likely lead to requests being rejected due to Retail Express API throttle limits.

If you have installed Magento according to the [user guide](http://devdocs.magento.com/guides/v2.0/install-gde/bk-install-guide.html) and installed Supervisor on Ubuntu 16.04, your Supervisor configuration file might look like:

```ini
[group:magento_user]
programs=magento_user_customers,magento_user_products
priority=1

[program:magento_user_customers]
user=magento_user
command=/var/www/html/magento2/bin/magento retail-express:command-bus:consume --max-memory=512 --max-runtime=300 --priority customers payments fulfillments
autorestart=true
numprocs=1
process_name=%(program_name)s_%(process_num)s
stdout_logfile=/var/log/supervisor/magento2/customers.log
stderr_logfile=/var/log/supervisor/magento2/customers.err.log

[program:magento_user_products]
user=magento_user
command=/var/www/html/magento2/bin/magento retail-express:command-bus:consume --max-memory=512 --max-runtime=300 --priority attributes price-groups products
autorestart=true
numprocs=2
process_name=%(program_name)s_%(process_num)s
stdout_logfile=/var/log/supervisor/magento2/products.log
stderr_logfile=/var/log/supervisor/magento2/products.err.log
```

> Because Queue Workers are long-running processes, they will not see any changes in the Magento codebase until they are restarted.
>
> **It is important to restart Supervisor if you make any changes to your Magento codebase (such as installing/updating any Magento extensions).**

#### 3.3.2. Cron

Queue Workers *(Section 3.3)* can be configured to run for a set period of time. This, paired with the frequency the Queue Worker is started on your cron schedule, can effectively emulate a continual background worker without the ability to use a process manager.

A typical example is scheduling a Queue Worker to run every 10 minutes, and make the Queue Worker run for only 9 minutes. This means the queue worker will exit before cron starts the next one. **Failure to do this would result in multiple queue workers running (until/if the Queue Worker crashes).**

If you have installed Magento according to the [user guide](http://devdocs.magento.com/guides/v2.0/install-gde/bk-install-guide.html), the following crontab entries will create a Queue Worker to sync Queued Commands for 9 minutes, then exit. It'll be started by cron every 10 minutes:

```bash
# 'crontab -e' as the Magento filesystem owner...
*/10 * * * * <path to php binary> <magento install dir>/bin/magento retail-express:command-bus:consume --max-runtime=540 --stop-when-empty --max-memory=512 --priority customers payments fulfillments
*/10 * * * * <path to php binary> <magento install dir>/bin/magento retail-express:command-bus:consume --max-runtime=540 --stop-when-empty --max-memory=512 --priority attributes price-groups products
```

#### 3.3.2. Manually

During development or debugging sessions, it is advisable to stop Supervisor or disable cron (depending on your chosen setup). This eliminates any Queue Workers you are not controlling from running which could hinder development or debugging.

To create a Queue Worker manually, it is recommended to provide a few extra arguments for granular control and clarity:

```bash
# Logged in as the Magento filesystem owner...

# Run a Queue Worker that provides the most detailed information when something wrong
bin/magento retail-express:command-bus:consume --stop-on-error --stop-when-empty -vvv customers
```

> Of course, there is the option to manually sync individual entities *(Section 3.2)* should you need to for debugging purposes.

### 3.4 Refreshing Queues

Occasionally, queued commands may fail. This may be due to issues with individual syncing or server environment.

In this case, there will be "stuck" queued commands in the `retail_express_command_bus_messages` table that appear to be reserved by a queue worker but never complete. This is because if a queue worker crashes, it does not get a chance to update the database accordingly. Most queue providers (such as [Amazon SQS](https://aws.amazon.com/sqs/)) will automatically add queued commands back into the queue after inactivity.

Fortunately, SkyLink supports a similar function, through the use of a CLI tool:

```bash

# Logged in as the Magento filesystem owner...
bin/magento retail-express:command-bus:refresh
```

There are two primary options available to customise this tool, both `--reserved` and `--attempts`:

```bash
Usage:
 retail-express:command-bus:refresh-queue [--reserved="..."] \
                                          [--attempts="..."]

Options:
 --reserved            Refresh jobs that have been reserved outside the given time in seconds (minimum 10 seconds) (default: 3600)
 --attempts            Flag to sync inline rather than queue a command (minimum 1 attempt) (default: 3)
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question
```

By default, this tool will:

1. Refresh any jobs that were reserved anytime outside the last hour; and,
2. Delete any jobs that have been attempted more than 3 times.

While it is absolutely fine to run this command manually, it is recommended to setup another [Magento cron job](http://devdocs.magento.com/guides/v2.1/config-guide/cli/config-cli-subcommands-cron.html) to automate this regularly:

```bash
# 'crontab -e' as the Magento filesystem owner...
*/5 * * * * <path to php binary> <magento install dir>/bin/magento retail-express:command-bus:refresh-queue
```

## 4. Performance

There are considerations to make with regards to performance of Magento when you are synchronising data, particularly with bulk synchronisations. These optimisations are recommended in any Magento installation, however their benefits are increasingly visible when running SkyLink. Recommendations for improving performance are:

1. Enable caching and production mode *(Section 2.5)*.
2. Enable [opcache](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-settings.html#php-required-opcache).
3. Configure [Magento's indexers](http://devdocs.magento.com/guides/v2.1/config-guide/cli/config-cli-subcommands-index.html#config-cli-subcommands-index-conf-set) to be on schedule and ensure [Magento cron jobs](http://devdocs.magento.com/guides/v2.0/config-guide/cli/config-cli-subcommands-cron.html) are running.
4. Use Redis for both [cache](http://devdocs.magento.com/guides/v2.0/config-guide/redis/redis-pg-cache.html) and [session](http://devdocs.magento.com/guides/v2.0/config-guide/redis/redis-session.html) storage.
5. Ensure your web server has enough resources available. Ensure that the total number of queue workers' memory limits plus the base [Magento memory requirement](http://devdocs.magento.com/guides/v2.1/install-gde/system-requirements-tech.html) does not exeed the server's memory capabilities.
6. Ensure your MySQL setup [is optimised](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/mysql.html) and consider separating your web server and database server.
7. Monitor your server load and identify areas that are underperforming. Magento typically is CPU-intensive and can also be disk I/O intensive (however our queue workers are designed to minimise disk I/O0.

## 5. FAQs

### There are no logs appearing in `System > SkyLink > Logging`

Ensure you have performed the temporary logging workaround *(Section 2.3.3)*.

### I see an error in my logs regarding `too many open files`

As the Queue Workers continually run, they invoke Magento which slowly consumes resources on the server. As a result, it's good practie to limit the time that a Queue Worker can run (so that your process manager may restart it) to free up resources. Magento was not designed originally to be continually run and as a result sometimes opens files on the system without closing them (they would normally be closed during a regular Magento page load).

Try decreasing the `max-runtime` of your queue worker *(Section 3.3)* and increasing the [open files limit](https://stackoverflow.com/a/34645) in your hosting environment.