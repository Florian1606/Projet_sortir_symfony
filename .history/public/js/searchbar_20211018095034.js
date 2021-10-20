function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    let sorties = document.getElementsByClassName('sortie');
    //console.log("tab : " + sorties_nom);
    console.log('tab : ');
    for (let sortie in sorties_nom) {
        if (Object.hasOwnProperty.call(object, key)) {
            const element = object[key];

        }
    }
    console.log(sorties_nom.length);
    // Si aucun résultat trouvé
    if (sorties_nom.length == 0) {
        // on cache le tableau
        let tab_sorties = document.getElementById('tab_sorties');
        tab_sorties.style.display = "none";
        // on le remplace par un "aucune résultat"
        let not_found = document.createElement
    } else {
        // On affiche le tableau si il a était enlevé
        tab_sorties.style.display = "";
        for (i = 0; i < sorties_nom.length; i++) {
            if (!sorties_nom[i].innerHTML.toLowerCase().includes(searchbarValue)) {
                // On cache la sortie
                sorties_nom[i].parentNode.style.display = "none";
            } else {
                // On laisse afficher la sortie
                sorties_nom[i].parentNode.style.display = "";
            }
        }
    }


}