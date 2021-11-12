# bread-virtual-meetings

The default presentation in [bread](https://github.com/bmlt-enabled/bread) for virtual meetings that are replacing an
in-person meeting seems potentially misleading, since it may be easy to overlook the TC key and go to a closed meeting
location. This extension provides some options for fixing those listings.  There are three options:
1. Add a note in front of the facility name: `Currently online only -- normally at`
2. Remove the facility name altogether, as well as the TC format
3. Don't change anything

Option 1 is closer to what crouton does.  Option 2 saves space on the printed schedule.
For options 1 and 2 this extension also removes any additional location information, the street address, and bus information,
since none of this is needed to get to a virtual meeting.

Option 3 is mostly useful for multisite environments -- otherwise it would be weird to be using this plugin at all.

There is a top-level administration menu item to select the option.  Making it a top-level menu is weird, but seems to be
the best of various bad choices.  Putting it under Meeting List (the bread menu) would be plausible, except that this option
isn't saved as part of the bread configuration, making it easy to overlook.  (Having bread save it would require some
additional hooks in bread itself -- easier would be to just add this functionality to bread.)  Putting it under "Settings"
would normally be good, except that bread uses a top-level menu item, and it seems like this one would get lost there.
