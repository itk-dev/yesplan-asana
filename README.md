# Yesplan-asana
Yesplan-asana is a project for copying eventinformation from Yesplan and Asana. It has been developed as a tool for a marketing department, to make sure that all event are advetised correctly. It is making use of customfields in both Yesplan and Asana.The custominformation used in Yesplan is used for filtering events, to make sure only the events that needs to be advitised is copied and created as cards on Asana.
Tasks are also added to a calendar board to get an overview of future and current event, when they go in presal, sale and is actually held. When presale, insale or event dates are changed in yesplan, a new card will show up in Asana. It is a manual process to delete the old one.
We have created 5 different event "filters":

Profile = Internal events:
  * 'LastMinute': LastMinute events is defined as events with capacitypercentage < 75, and event is scheduled int the next 3 weeks
  * 'FewTickets': FewTickest events is defined as events with capacitypercentage > 90
  * 'EventsOnline': EventsOnline events is defined as events with eventOnline from Yesplan = 1
  * 'Events': is defined as events with productionOnline from Yesplan = 1

Profile = External and free events:
  * 'EventsExtern': is defined as events with productionOnline from Yesplan = 1

We only filter on events with the status = I salg/offentliggjort.

## Installation

```sh
docker-compose up -d
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

## Environment variables
To make the project integration with Yesplan and Asana, some environment variables need to be configured:

Get Yesplan apikey from yesplan, yesplan URL should be somthing like this: https://XXXX.yesplan.be/
* YESPLAN_APIKEY=''
* YESPLAN_URL=''

ProfleID of different event profiles in Yesplan (used for sorting and filtering of events, before import to Asana)
* YESPLAN_INTERN_PROFILEID=''
* YESPLAN_EXTERNAL_PROFILEID=''
* YESPLAN_FREE_PROFILEID=''

StatusID of the status needed imported (only events with this statusID will be imported)
* YESPLAN_STATUSID=''

LocationIDs of the locations needed to imported (ID's in commaseperated list).
YESPLAN_LOCATIONIDS=''

Get the Asana Bearer from authorized Asana app. The Asana URL should probably be this: https://app.asana.com/api/1.0/tasks
* ASANA_BEARER=''
* ASANA_URL=''

The following should contain the ID (ID's in commaseperated list) to the board(s) where cards will be created:
* ASANA_NEW_EVENT=''
* ASANA_NEW_EVENT_ONLINE=''
* ASANA_LAST_MINUTE=''
* ASANA_FEW_TICKETS=''
The following should contain the ID to the board you want to use in calendar view to show events on the presale date, in sale date and on the event date:
* ASANA_CALENDAR=''

The following should contain the id to the custom Asana field, where you want the information from Yesplan:
* YESPLAN_ID_FIELD=''
* YESPLAN_EVENTDATE_FIELD=''
* YESPLAN_LOCATION_FIELD=''
* YESPLAN_GENRE_FIELD=''
* YESPLAN_MARKETINGBUDGET=''
* YESPLAN_PUBLICATIONDATE_FIELD=''
* YESPLAN_PRESALEDATE_FIELD=''
* YESPLAN_INSALEDATE_FIELD=''
* YESPLAN_PERCENT_FIELD=''
* YESPLAN_PROFILE_FIELD=''
* YESPLAN_STATUS_FIELD=''
* ASANA_CALENDAR_COLOR_FIELD=''

The following should contain the ID of the colorvaluefields used by the colorfield (used to show different colors in the calendar):
ASANA_CALENDAR_COLOR_FIELD_RED=''
ASANA_CALENDAR_COLOR_FIELD_GREEN=''
ASANA_CALENDAR_COLOR_FIELD_YELLOW=''


This should contain SMTP address for error mail sending
* MAILER_DSN=smtp://localhost

Information on who should receive error mails (to), email address this is sent from, and a prefix for the email subject, could be "dev", "production" or whatever you like.
* MAIL_TO=''
* MAIL_PREFIX=''
* MAIL_FROM=''

## Usage

```sh
docker-compose exec phpfpm bin/console app:yesplan:get-events
```
```sh
docker-compose exec phpfpm bin/console app:yesplan:delete-old-events
```

```sh
docker-compose exec phpfpm bin/console app:asana:create-cards
```

## Coding standards

```sh
docker-compose exec phpfpm composer coding-standards-check
```

```sh
docker-compose exec phpfpm composer coding-standards-apply
```