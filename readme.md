## Running Wordpress on Openshift

The following steps walk you through the recommended wordpress installation:

### Step 1: Access the OpenShift user interface and log in from the CLI

Open the OpenShift console and enter your credentials to login. On the web user interface of OpenShift’s cluster in the top right corner on the service catalog page, click on your credentials and select Copy Login Command. Open your CMD/Terminal and paste the login credentials. You are now successfully logged in to the OpenShift Container Platform server using the oc CLI.

Note: The generated token is a type of password. Do not share it with others.

### Step 2: Create a new project

Create a project to host your deployments and resourcesby running the following command:

```
oc new-project intranet
```

### Step 3: Deploy a persistent mariadb database

Now you need to create the back-end database instance, which is in our case MariaDB. Therefore, run the following command:

```
oc new-app mariadb-persistent -e MYSQL_USER=redhat -e MYSQL_PASSWORD=openshift -e MYSQL_DATABASE=wordpress
```

Note: MariaDB persistent is used so that any data stored is not lost when pods are destroyed.

### Step 4: Deploy the Wordpress application on OpenShift and configure it

In order to deploy WordPress, build your project on Apache with a PHP image, by using this command:

```
oc new-app php~https://github.com/iraftoul/wordpress
```

After you successfully deployed WordPress, you need to access it. You expose it as a service using the following command:

```
oc expose svc/wordpress
```

Next, query the service route for the host URL that is generated. Run the following command:

```
oc get routes
```

Copy the generated host name from your terminal and paste it in any browser. You should see the welcome screen of the deployed WordPress application. Provide the following information:

Site title, username, password and your email.

After successfully setting up WordPress, the login screen opens. Use the username and password you provided to log in.

You can now start editing on Wordpress.

For more information feel free to have a look at this post:

https://developer.ibm.com/languages/php/tutorials/build-deploy-wordpress-on-openshift/
