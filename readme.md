The following steps walk you through the recommended wordpress installation:

Step 1: Access the OpenShift user interface

Open the OpenShift console and enter your credentials to login.

From the cluster’s web console, you see on the top right that you are now automatically authenticated, logged in, and have established a session to the OpenShift Container Platform server with your credentials:

Now use the OpenShift cli to log in and create a project.

On the web user interface of OpenShift’s cluster in the top right corner on the service catalog page, click on your credentials and select Copy Login Command. It generates a live session token with your logged in credentials on the OpenShift container platform server.

Note: The generated token is a type of password. Do not share it with others.

Step 2: Log in from the CLI

Open your CMD/Terminal and paste the login credentials. The command consists of the oc login + the server URL attached with the live session generated token.

You are now successfully logged in to the OpenShift Container Platform server using the oc CLI. It shows a list of the available OpenShift projects.

Step 3: Create a project

Create a project to host your deployments and resources.
From CMD/terminal, run the following command:

oc new-project intranet

Step 4: Deploy your database and your WordPress app on OpenShift

After creating a project, you need to deploy your WordPress application.

First, you need to create the back-end database instance, which is in our case MariaDB. From the CMD/terminal, run the following command:

```
oc new-app mariadb-persistent -e MYSQL_USER=redhat -e MYSQL_PASSWORD=openshift -e MYSQL_DATABASE=wordpress
```

Note: MariaDB persistent is used so that any data stored is not lost when pods are destroyed.

Take a note or your MariaDB-generated information: MariaDB connection user name, MariaDB connection password, and MariaDB database name.

Because you are going to deploy WordPress, build your project on Apache with a PHP image.

From the CMD/terminal run the following command:

```
oc new-app php~https://github.com/iraftoul/wordpress
```

You can track the deployment by viewing its logs:

oc logs -f dc/wordpress

After you successfully deployed WordPress, you need to access it. You expose it as a service using the following command:

```
oc expose svc/wordpress
```

Next, query the service route for the host URL that is generated. Run the following command:

```
oc get routes
```

Copy the generated host name from your terminal and paste it in any browser. You should see the welcome screen of the deployed WordPress application.

Now, you have deployed your two resources under one project: MariaDB and WordPress.

You can get a visual view by going back to your Redhat OpenShift console web user interface. Click Refresh and you successfully see your newly created project listed on the My Projects tab, including the two created resources.

After successfully setting up WordPress, the login screen of WordPress opens. Use the following username and password and log in:

username: redhat
password: openshift
