# member_database
An application in php with mariadb backend. 

Record keeping for the makerspace: member details, inventory, inductions, repairs, loans and attendance.

**Functional description**

The main areas of record keeping are: 
* Name & contact details for every member
* An inventory of all tools and equipment, with links to further information

This allows for further record-keeping, if required:
* What inductions each member has completed
* Identifying what needs repair, and what repairs have been completed
* Any tools borrowed or returned
* Daily attendance recording
* Formal labelling of members' own property left in the workshop

Extra functions can be added in the future:
* Subscription and donation recording
* RFID membership card for door-access, check-in and machine activation

Each member is uniquely identified by a unique nickname, typically the first-name and the initial letter of the last-name.  But not restricted to that.  

No login or password is required to use the system, but it's expected that you will identify yourself with your unique name when using it.  The system keeps contact details (address, phone and email) hidden, for privacy purposes.  Contact details will be used if we have no other way to get in touch with you, should this be necessary.

Members are trusted to use the system responsibly, identifying themselves with their registered nickname, and not using someone else's name.  It is considered that requiring individual password authentication detracts from ease of use.

Any person recognised as an **administrator** of the makerspace can have a password-protected log-in. 

When logged in as an administrator, a number of additional functions become available.  The contact details for any member can be seen, or edited.  Inventory, fault reports and tool loan records can be edited.  New admins can be added to the system by an existing admin.

(The system keeps a log of every transaction, and issues an email notification when a fault report is recorded.  There is a daily, weekly and monthly summary of attendance.) (These features are not currently provided but will be added)

**Technical requirements**

An always-on computer, on the makerspace network running a web server (Apache2, Nginx, Lightppd), an SQL database (Mysql or Mariadb), and PHP.    A Raspberry Pi devoted to this function alone would be adequate.

It's desirable that the system is not readily available beyond the makerspace network, for best security.

Within the makerspace, it is desirable to have one computer terminal exclusively available for accessing the system.  Access to the system should also be possible from other computers in the building, and from mobile phones while on the local WiFi.

The server should be able to make daily backups, to be copied to a system outside the building.

**Design philosophy**

The software package has been written in PHP to keep it simple.  Anyone who is familiar with HTML and SQL should be able to understand and extend the software.

Each one of the main functions is written as an individual PHP file.  Within the file, the function consists of a number of stages, e.g. select; process; save.  

Data is passed as POST variables from html forms, and data is saved between stages as SESSION variables.  All SESSION variables are cleared on returning to the main home page, but not cleared on return to the admin home page.

To keep the indivdual PHP files readable, the stages call functions from an include file.  It is within the include file that the detail of the work is carried out.

At any time, the user can click on the Home link, to abandon the current process.  This will never result in data loss or corruption.

Within the Administration pages, there is a separate link to return to the Admin home page, while remaining logged-in as admin.  This Admin home link can also be used to abandon a part-completed process, with no corruption.  

If a logged-in administrator clicks on the Home link, they will be logged out and returned to the main home page.
