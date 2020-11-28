# Web GIS for the Optimization of Solid Waste Collection and Transport: Case Study of Babezzouar, Algeria
## Abstract
In the context of a **web GIS** (Geographic Information System), this study focuses on the application of the Vehicle Routing Problem to calculate the best routes (shortest route, fastest route, etc.) of a fleet of vehicles to serve (collect and transport) a set of collection points of an agglomeration (geographic database of collection points).
## System modeling (UML diagrams)
![Class diagram](https://github.com/raouftams/pfe/blob/master/UML/ClassDiagram.jpg?raw=true)
![Usecase diagram](https://github.com/raouftams/pfe/blob/master/UML/usecase1.jpg?raw=true)
![Usecase diagram](https://github.com/raouftams/pfe/blob/master/UML/usecase2.jpg?raw=true)

## Vehicle routing problem
In this study we are using the **CVRPTW** (Capacitated Vehicle Routing Problem with time windows) because it produces a solution that respects business constraints (time windows, road traffic, tonnage, volume, speed, type of waste, collection frequency, condition of the road, etc.) while reducing operational costs (mileage, working time, fuel consumption, etc.)

To use the vehicle routing problem in our app your have to:

- Create an [ArcGis Developer](https://developers.arcgis.com/) account and get a token
- Paste the token in AppController file 
    ```
    pfe/
    |__src/
    |  |__controller/
    |  |  | AppController.php -> attribut $token;
    ```


## Technologies

- [Symfony](https://symfony.com): backend php framework 
- [Jquery](https://jquery.com/): Javascript library
- [LeafletJs](https://leafletjs.com/): Javascript mapping library
- [Bootstrap](https://getbootstrap.com/): CSS framework
- [ArcGis Rest APIs](https://developers.arcgis.com/labs/browse/?product=rest-api&topic=any): VRP and Routing sevices

## Some images of the app
![Home page](https://github.com/raouftams/pfe/blob/master/Application%20images/accueil.PNG?raw=true)
![Dashboard](https://github.com/raouftams/pfe/blob/master/Application%20images/dashboard.png?raw=true)
![Planification](https://github.com/raouftams/pfe/blob/master/Application%20images/addrotation.png?raw=true)
![Balance sheet](https://github.com/raouftams/pfe/blob/master/Application%20images/bilan.PNG?raw=true)
