var EsriLeafletRouting = {
		WorldRoutingService: 'https://route.arcgis.com/arcgis/rest/services/World/Route/NAServer/Route_World/',
	};

	// attach to the L.esri global if we can
	if(typeof window !== 'undefined' && window.L && window.L.esri) {
		window.L.esri.Routing = EsriLeafletRouting;
	}

	// We do not have an 'Esri' variable e.g loading this file directly from source define 'Esri'
	if(!Esri){
		var Esri = window.L.esri;
	}