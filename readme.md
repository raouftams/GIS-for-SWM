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

# Vehicle routing problem
 To use the vehicle routing problem in our app your have to:

- Create an [ArcGis Developer](https://developers.arcgis.com/) account and get a token
- Paste the token in VRPController file 
    ```
    pfe/
    |__src/
    |  |__controller/
    |  |  |__User/
    |  |  |  | VRPController.php -> function index();
    ```


# Technologies

- [Symfony](https://symfony.com): backend php framework 
- [Jquery](https://jquery.com/): Javascript library
- [LeafletJs](https://leafletjs.com/): Javascript mapping library
- [Bootstrap 4](https://getbootstrap.com/): CSS framework
- [ArcGis Rest APIs](https://developers.arcgis.com/labs/browse/?product=rest-api&topic=any): VRP and Routing sevices




