var Depots = [];
var Vehicules = [];
var Points = [];

function startFix(element){
element.className = element.className.replace(new RegExp('(?:^|\\s)'+ 'is-invalid' + '(?:\\s|$)'), ' ');
}


function checkdepot()
{
    var Couche = document.getElementById('Couche');
    startFix(Couche);
    if(Couche.value === '')
    {  
        Couche.classList.add('is-invalid');
        return 0;
   }
    
    
    var hd = document.getElementById('hd');
    startFix(hd);
    if(hd.value==='')
    {
        hd.classList.add('is-invalid');
        return 0;
    }

    var hf = document.getElementById('hf');
    startFix(hf);
    if(hf.value ==='')
    {
        hf.classList.add('is-invalid');
        return 0;
    }
    if(hd.value.localeCompare(hf.value) !== -1)
    {
        hf.classList.add('is-invalid');
        hd.classList.add('is-invalid');
        return 0;
    }

    var Depot = {
        nom : Couche.options[Couche.selectedIndex].text,
        code : Couche.value, 
        heureDebut : hd.value, 
        heureFin : hf.value
    };

    var selectDepot = document.getElementById("Couche");
	for (let i = 0; i < selectDepot.length; i++) {
		if (selectDepot.options[i].value == Couche.value) {
            selectDepot.remove(i);
        }
    }

    return Depot;

}


function checkvehicule()
{
    var Code = document.getElementById('code');
    startFix(Code);
    if(Code.value === '')
    {     
        Code.classList.add("is-invalid")
        return 0;
    }


    var DepotDepart = document.getElementById('DepotDepart');
    startFix(DepotDepart);
    if(DepotDepart.value === '')
     {     
        DepotDepart.classList.add('is-invalid');
        return 0;
    }
    var DepotFin = document.getElementById('DepotFin');
    startFix(DepotFin);
    if(DepotFin.value === '')
     {     
        DepotFin.classList.add('is-invalid');
        return 0;
    }

    var HeureDebut = document.getElementById('HeureDebut');
    startFix(HeureDebut);
    if(HeureDebut.value ==='')
    {
        HeureDebut.classList.add('is-invalid');
        return 0;
    }
    var HeureFin = document.getElementById('HeureFin');
    startFix(HeureFin);
    if(HeureFin.value ==='')
    {
        HeureFin.classList.add('is-invalid');
        return 0;
    }

    if(HeureDebut.value.localeCompare(HeureFin.value) !== -1)
    {
        HeureFin.classList.add('is-invalid');
        HeureDebut.classList.add('is-invalid');
        return 0;
    }
    
    var ChargeHoraire = document.getElementById('ChargeHoraire');
    startFix(ChargeHoraire)
    if(ChargeHoraire.value ==='')
    {
        ChargeHoraire.classList.add('is-invalid');
        return 0;
    }

    var NbrMaxOrdres = document.getElementById('NbrMaxOrdres');
    startFix(NbrMaxOrdres)
    if(NbrMaxOrdres.value ==='' || parseInt(NbrMaxOrdres.value) === 0)
    {
        NbrMaxOrdres.classList.add('is-invalid');
        return 0;
    }

    var costUnitTime = document.getElementById('costUnitTime');
    startFix(costUnitTime)
    if(costUnitTime.value ==='')
    {
        costUnitTime.classList.add('is-invalid');
        return 0;
    }

    var costUnitTime = document.getElementById('costUnitTime');
    startFix(costUnitTime)
    if(costUnitTime.value ==='')
    {
        costUnitTime.classList.add('is-invalid');
        return 0;
    }

    var costUnitDistance = document.getElementById('costUnitDistance');
    startFix(costUnitDistance)
    if(costUnitDistance.value ==='')
    {
        costUnitDistance.classList.add('is-invalid');
        return 0;
    }

    var renewalDepot = document.getElementById('renewalDepot').value;
    

    var Vehicule = { 
        code : Code.value,
        depotDepart : DepotDepart.value,
        depotFin : DepotFin.value,
        heureDebut : HeureDebut.value,
        heureFin : HeureFin.value,
        chargeHoraire : ChargeHoraire.value,
        nbrMaxOrdres : NbrMaxOrdres.value,
        costUnitTime : costUnitTime.value,
        costUnitDistance : costUnitDistance.value,
        renewalDepot: renewalDepot
    };

    var selectVehicle = document.getElementById("code");
	for (let i = 0; i < selectVehicle.length; i++) {
		if (selectVehicle.options[i].value == Code.value) {
            selectVehicle.remove(i);
        }
    }
    
    return Vehicule;
}



function AjouterDepot()
{   
    var depot = checkdepot();
    if( depot === 0 ){return 0;} // sinon ça continue a foirer
    document.getElementById("depotsForm").reset();
    Depots.push(depot);
    
    var sel1 = document.getElementById('DepotDepart');
    var sel2 = document.getElementById('DepotFin');
    var sel3 = document.getElementById('renewalDepot');
    var opt = document.createElement('option');
    opt.appendChild( document.createTextNode(depot['nom']));
    opt.value = depot['nom'];
    var opt2 = document.createElement('option');
    opt2.appendChild( document.createTextNode(depot['nom']));
    opt2.value = depot['nom']; 
    var opt3 = document.createElement('option');
    opt3.appendChild( document.createTextNode(depot['nom']));
    opt3.value = depot['code'];   
    sel1.appendChild(opt2); 
    sel2.appendChild(opt);
    sel3.appendChild(opt3);
}


function AjouterVehicule()
{
    var Vehicule = checkvehicule();
    if(Vehicule === 0) {return 0;}
    document.getElementById("vehicleForm").reset();
    Vehicules.push(Vehicule);
    
}

     
    
    

//****************************************************************************************************************************** */
