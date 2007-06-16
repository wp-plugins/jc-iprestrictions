=== Plugin Name ===

Contributors: joshcook
Donate link: http://www.joshcook.net/
Tags: restrict, ip, block, access, password, network, ban, restriction, time, daily, workday
Requires at least: 2.0.4
Tested up to: 2.2
Stable tag: trunk

Provides the ability to restrict access to your web site by IP address and/or IP network.

== Description ==

Restrict access to your web site by IP address and/or network, with the ability for each restriction entry to be based on day(s) of week and/or time of day.  In addition, each restriction can also have a seperate message displayed to the end-user and a password in which the restriction can be bypassed.

Features:

* Single IP addresses or entire networks (using netmask) can be blocked
* Block every day or on selected days during the week
* Always block access or just during specified time
* Bypass password configurable for each restriction entry
* Custom message for each restriction entry
* Global restricted message, with replacement variables for detailed entry information

Visit [www.joshcook.net](http://www.joshcook.net/ "www.joshcook.net") for update information or to submit feature and support requests.

== Installation ==

1. Upload the 'jc-iprestrictions' directory to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in Wordpress
3. Configure JC-IPRestrictions from the 'Manage -> IP Restrictions' menu option

== Configuration ==

**Mode**

No Restrictions
> *Off.  No blocking what-so-ever.*

Actively Restricting
> *On.  Actively blocking those that are configured to be blocked.*

**IP Restrictions**

IP Restrictions allow an unlimited amount of restriction entries, each being configured on its own line.  Each entry has several configurable parameters that are separated by the pipe "|" character.

Parameters

> Net
> > *The IP to block or the beginning of the IP network to block. (required)*
> 
> Mask
> > *The netmask of the IP/network to block.  Defaults to 255.255.255.255. (optional)*
> 
> Day
> > *Specify which day(s) the site is restricted.  Not including or leaving this field blank indicates that all days are restricted.  Specified in numeric notation 0 through 6, where Sunday is 0 and Saturday is 6. (optional)*
> 
> Begin
> > *If the restriction is time sensitive, this is when the restriction begins.  Specified in military time. (optional)*
> 
> End
> > *If the restriction is time sensitive, this is when the restriction ends.  Specified in military time. (optional)*
> 
> Password
> > *If provided, allows the end-user to bypass the restriction. (optional)*
> 
> Message
> > *A short restriction message.  If you enter any of the above parameters in brackets ([]), they will be replaced with the corresponding parameter value. (optional)*

Examples:

> `Net=127.0.0.1`
> 
> > *Restricts the IP address of 127.0.0.1 from accessing the web site*
> 
> `Net=127.0.0.1|Mask=255.255.255.0|Begin=08:00|End=18:00`
> 
> > *Restricts the entire class-c network (from 127.0.0.0 - 127.0.0.255) between 8:00 AM and 6:00 PM*
> 
> `Net=127.0.0.1|Mask=255.255.255.0|Day=12345|Begin=08:00|End=18:00`
> 
> > *Restricts the entire class-c network (from 127.0.0.0 - 127.0.0.255) Monday through Friday between 8:00 AM and 6:00 PM*
> 
> `Net=127.0.0.1|Mask=255.255.255.0|Password=abcd|Message=You've been blocked!`
> 
> > *Restricts the entire class-c network (from 127.0.0.0 - 127.0.0.255) with the ability for the viewer to bypass the restriction if they know the password.  In addition, a custom restricted message is included.*

Notes:

* An entry can be commented out by using the splat "*" character.
* Entries with a greater net specificity should be listed higher in the list.
* The entry parameters are not case sensitive.
* Keep in mind the difference between your server's time and the time of your local workstation (or the IP address(es) you are blocking).
* If only begin or end is included, the other is automatically assigned.  If begin is missing it is set to 00:00 (12 AM), while end is set to 23:59 (11:59 PM).

**Restricted Message**

The restricted message can be either plain text or html, although PHP code is not processed.  All of the parameters from above can be included dynamically if surrounded in brackets ([]), in addition to two new parameters:

> Timestamp
> > The current time the restriction was processed.  Useful for debugging.
> 
> Form
> > If a password parameter exists for the restricted entry, a bypass input box is displayed so the viewer can bypass the restriction.  The form input box has a CSS ID of `jc_ipr_css_input` in order to customize.

The preview link is available to verify that the message will display correctly, just to make sure all those path statements are right. ;)  A default message is used if leave the Restricted Message field empty.

Example:

> A restricted message of:
> 
> > `We're sorry, the IP network of [NET] ([MASK]) has been restricted from viewing our web site for the following reason: [Message]`
> 
> For the IP Restriction entry:
> 
> > `Net=127.0.0.1|Mask=255.255.255.0|Begin=08:00|End=18:00|Message=You should be working between the time of [BEGIN] and [END]!!!`
> 
> Would display:
> 
> > `We're sorry, the IP network of 127.0.0.1 (255.255.255.0) has been restricted from viewing our web site for the following reason: You should be working between the time of 08:00 and 18:00!!!`

== Advanced Configuration ==

There are three configurable options within the jc-iprestrictions.php file:

> `$jc_ipr_v_DelimiterChar`
> > *The delimiter character used for IP Restrictions entries.  (| by default)*
> 
> `$jc_ipr_v_CommentChar`
> > *The comment character used for IP Restrictions entries.  (\* by default)*
> 
> `$jc_ipr_v_DateFormat`
> > *The format for the [Timestamp] parameter processed in the restricted message.*

You'll probably never need to change these...

== Frequently Asked Questions ==

= Why was this plugin created? =

I didn't want people from my place of employment to visit my web sites during work hours.

= Where can I get support for this plugin? =

I provide support via my [web site](http://www.joshcook.net "www.joshcook.net"), although just because you post a request for help doesn't mean I will have time to answer your question. This is a free plugin and as such, you aren't guaranteed support. Then again, I'll help as much as I can and have time for.

= How do I find the IP address(es) for a certain location? =

If you're not at the location, you can use common tools such as the IP information included with comments, your server's log files, etc.  If you are the location you want to restrict you can use an online tool such as [IP Chicken](http://www.ipchicken.com/ "IP Chicken").  Finding the netmask for the location is a little more difficult and outside the scope of this FAQ; you might just want to ask your network administrator or Internet provider.

= Why is the `jc_ipr_c_RestrictIP` cookie used? =

In order to reduce processing time, the cookie is given to any browser that isn't being restricted.

= Your plugin rocks!  Can I donate? =

Absolutely!!!  Visit [web site](http://www.joshcook.net "www.joshcook.net") for more information!

== Screenshots == 

1. Plugin configuration page

== Version History ==

[0.1]

Internal Release

[0.2]

Internal Release

- Added auth cookie
- Added begin and end block time
- Added custom message for each IP entry

[0.3]

Internal Release

-	Added bypass feature by password
- Added restricted message (template)
- Added replacement variables for restricted message (template)

[0.4]

Internal Release

- Added bypass option for each IP entry
- Fixed custom restricted message encoding

[0.5]

Internal Release

- Rewrote bypass to not allow if password is blank
- Removed test mode

[0.6]

Internal Release

- Added preview for restricted message (template)

[0.7]

Internal Release

- Added form input CSS
- Removed default global bypass password
- Removed bypass feature for each item
- Added global variables for items that probably won't be changed
- Added ability for each entry to have it's own password
- Added DATESTAMP variable for output
- Changed cookie to be entire domain name instead of siteurl
- Added difference in time message on options page
- Changed cookie expire to one hour

[0.8]

Internal Release

- Added default restriction message (html message)
- Added default restriction message (function)
- Added ability to restrict by day

[1.0]

First Public Release

[1.1]

Public Release

- Rewrote preview option for the Restricted Message
- Fixed plugin activate action
- Added restore default restricted message checkbox