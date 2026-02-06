# Usage

## First Ticket (Quick Steps)

1. Organisations -> New Organisation
2. Provide name and email domain.
3. Sites -> New Site
4. Provide site name/identifier (optional).
5. Provide street address (required).
6. Provide street address 2 (optional).
7. Provide town (optional).
8. Provide city (required).
9. Provide postal code (required).
10. Provide telephone (required). Phone numbers can only contain digits 0-9, a space and ( )+. characters and can be a maximum of 20 characters.
11. Contacts -> New Contact
12. Provide first name (required).
13. Provide surname (required).
14. Provide work email (required).
15. Click your name in the nav bar -> Admin.
16. Business Hours -> Create Business Hours.
17. Use defaults (name, time zone, Mon-Fri 9am-5pm, Sat/Sun unticked) and submit.
18. SLA -> Create SLA and assign the Business Hours you created.
19. SLA is optional if you are not using due-date calculations.
20. A default "Standard" SLA and "Default (Mon-Fri 9-5)" business hours are created by migration and can be edited.
21. Organisations -> select your organisation -> click "assign SLA" and choose "Standard".
22. Click the SLA link to view the policy and confirm the targets.
23. Due dates are calculated using business hours only (e.g., a Friday evening ticket will roll to Monday 09:00 before SLA time is counted).
24. Organisations -> select your organisation -> Create Ticket.
25. Fill in Summary, Source, Type, Impact, Urgency, Due By (auto if SLA), and Description.
26. Request user, Queue, and Site will auto-select if only one exists.
27. Save the ticket and verify it appears in the ticket list.
28. Resolved tickets are closed by the scheduled command `ticket:close-resolved` (see `docs/cron.md`).

## User Management (Quick Steps)

1. Click your name in the nav bar -> Admin -> Users.
2. Use User Manager to add or edit users.
3. Use Role Manager to view roles and edit role permissions.
4. Click a role name -> Edit Role Permissions to check/uncheck permissions, then Save.
5. Use Permission Manager to create new permissions when needed.
