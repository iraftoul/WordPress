## Running Wordpress on Openshift

The following steps walk you through a recommended way for installing wordpress:

### Step 1: Access the OpenShift UI and log in from the CLI

Open the OpenShift console and enter your credentials to login. On the web user interface of OpenShift’s cluster in the top right corner on the service catalog page, click on your credentials and select Copy Login Command. Open your CMD/Terminal and paste the login credentials. You are now successfully logged in to the OpenShift server CLI.

Note: The generated token is a type of password. Do not share it with others.

### Step 2: Create a new project

Create a project (named intranet) to host your deployments and resources by running the following command:

```
oc new-project intranet
```

### Step 3: Deploy a persistent mariadb database

Now you need to create the back-end database instance, in our case MariaDB. Therefore, run the following command:

```
oc new-app mariadb-persistent -e MYSQL_USER=redhat -e MYSQL_PASSWORD=openshift -e MYSQL_DATABASE=wordpress
```

Note: MariaDB persistent is used so that any data stored is not lost when pods are destroyed.

### Step 4: Deploy the Wordpress application on OpenShift

In order to deploy WordPress, build your project on Apache with a PHP image, by using this command:

```
oc new-app php~https://github.com/iraftoul/wordpress
```

After successfully deploying WordPress, you need to access it. Expose it as a service using the following command:

```
oc expose svc/wordpress
```

Next, query the service route for the host URL that is generated. To do so, run this command:

```
oc get routes
```

Copy the generated host name and paste it in any browser. You should see the welcome screen of WordPress.

### Step 5: Configure the Wordpress application

In the welcome screen, provide the following information to complete the wordpress initialization:

_Site title | Username | Password | Your email_

After setting up WordPress, the login screen opens. Use the username and password you provided to log in. Everything should be now set up and you can start editing on Wordpress.

For more context and input on the topic feel free to go through this recent post by IBM:
https://developer.ibm.com/languages/php/tutorials/build-deploy-wordpress-on-openshift/
