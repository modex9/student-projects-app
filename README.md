On the main page, user can view the list of projects and their information.
Also, new projects can be created. When creating a project, user must enter a positive number of groups and
max number of students that will be in one of project's groups.

After project is created, a specified number of groups is also created, with number of empty slots that were specified in max number of students per group field.

In projects list, there is a status link, which directs user to project's status page.
In status page, user can add students to the project. After students are added, they can be assigned to any of the groups that were created. Upon successful assigning of a student, a success message will appear. Otherwise, an error message will be displayed. When all slots in a group are filled, a message above the group will be displayed, informing that there are no more slots in the group.
Regardless to which group's slot student is assigned, after page/content refresh, the slots are filled from top to bottom.

If a student is deleted from a project, it will also be removed from a group.
All content in the page is refreshed every 10 seconds by an Ajax request.

To test the functionality, controller tests were written for various CRUD cases, as well as student assigment to group case. Student's removal is also tested, among with relationship with project and student's group.

Requirements to launch the App:
 - PHP 8.1
 - MySQL 8
 - Composer 2
 - Npm
 - Symfony CLI

To launch this App follow these steps:
 - Clone this repo or download the .zip of the repository.
 - Open the directory where you cloned the project in the terminal.
 - Run `composer install`
 - Run `npm install --save-dev`
 - Run `npm run build`
 - Inside `.env` file change parameters `user`, `password` and `db` with your Database Username, password and Database Name used for this project.
  - Execute database migrations by running `php bin/console doctrine:migrations:migrate`
  - Launch the app by running `symfony server:start`
  - Go to `http://localhost:8000` to use the app.