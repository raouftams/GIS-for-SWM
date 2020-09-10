# PFE Licence

# Installation
- Download or clone the repository
    ```sh
    $ git clone https://github.com/raouftams/pfe.git
    ```
- Go to the application root (pfe) and run this command
   ```sh
    $ symfony server:start
    ```
# Database connection
Our application uses php PDO for database connection, you can change the database configuration in :
```
    pfe/
    |__src/
    |  |__Core/
    |  |  |__Database/
    |  |  |  | PostgresDatabase.php -> method getPDO();
```
# Vehicle routing problem
 To use the vehicle routing problem in our app your have to:

- Create an [ArcGis Developer](https://developers.arcgis.com/) account and get a token
- Paste the token in AppController file 
    ```
    pfe/
    |__src/
    |  |__controller/
    |  |  | AppController.php -> attribut $token;
    ```


# Technologies

- [Symfony](https://symfony.com): backend php framework 
- [Jquery](https://jquery.com/): Javascript library
- [LeafletJs](https://leafletjs.com/): Javascript mapping library
- [Bootstrap 4](https://getbootstrap.com/): CSS framework
- [ArcGis Rest APIs](https://developers.arcgis.com/labs/browse/?product=rest-api&topic=any): VRP and Routing sevices




