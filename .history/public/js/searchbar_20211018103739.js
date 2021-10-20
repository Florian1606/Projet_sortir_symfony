function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    let sorties = document.getElementsByClassName('sortie');

    // On affiche le tableau si il a était enlevé
    tab_sorties.style.display = "";

    // En fonction du nom 
    for (i = 0; i < sorties_nom.length; i++) {
        if (!sorties_nom[i].innerHTML.toLowerCase().includes(searchbarValue)) {
            // On cache la sortie
            sorties_nom[i].parentNode.style.display = "none";
            console.log(isEmpty(sorties_nom));
            if (isEmpty(sorties_nom)) {
                deleteTable();
            }
        } else {
            // On laisse afficher la sortie
            sorties_nom[i].parentNode.style.display = "";
            if (!isEmpty(sorties_nom)) {
                showTable();
            }
        }
    }
}


function deleteTable() {
    console.log("dDDD")
        // on cache le tableau
    let tab_sorties = document.getElementById('tab_sorties');
    tab_sorties.style.display = "none";
    // on le remplace par un "aucun résultat"
    let not_found = document.createElement('h1');

}

function showTable() {
    // On supprime l'élement ajouté "Aucun résultat"

    // On affiche le tableau
    let tab_sorties = document.getElementById('tab_sorties');
    tab_sorties.style.display = "";
}



function isEmpty(sorties_nom) {
    isEmptyBool = true;
    // Vérification si le tableau est vide
    for (let sortie of sorties_nom) {
        if (sortie.parentNode.style.display != "none") {
            isEmptyBool = false
        }
    }
    return isEmptyBool;
}