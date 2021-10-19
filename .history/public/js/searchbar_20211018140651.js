function search_sorties(e) {
    let searchbarValue = document.getElementById('searchbar').value.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');

    // On affiche le tableau si il a était enlevé
    tab_sorties.style.display = "";

    // En fonction du nom 
    searchNom(sorties_nom, searchbarValue, ville);
    // En fonction de la ville
    //searchVille(ville);
}

function searchNom(sorties_nom, searchbarValue, ville) {
    let sorties_ville = document.getElementsByClassName('villeRatachement');
    for (i = 0; i < sorties_nom.length; i++) {
        console.log()
        if ((!sorties_nom[i].innerHTML.toLowerCase().includes(searchbarValue)) ||
            (sorties_ville[i].value != ville || ville != "")
        ) {
            // On cache la sortie
            sorties_nom[i].parentNode.style.display = "none";
            if (isEmpty(sorties_nom)) {
                deleteTable();
            }
        } else if ((sorties_ville[i].value == ville || ville == "")) {
            // On laisse afficher la sortie
            sorties_nom[i].parentNode.style.display = "";
            if (!isEmpty(sorties_nom)) {
                showTable();
            }
        }
    }
}


function searchVille(e) {
    let ville = e == null ? null : e.value;
    console.log(ville);
    let sorties_ville = document.getElementsByClassName('villeRatachement');
    for (i = 0; i < sorties_ville.length; i++) {
        if (sorties_ville[i].value !== ville && ville !== "") {
            // On cache la sortie
            sorties_ville[i].parentNode.style.display = "none";
            if (isEmpty(sorties_ville)) {
                deleteTable();
            }
        } else {
            // On laisse afficher la sortie
            sorties_ville[i].parentNode.style.display = "";
            if (!isEmpty(sorties_ville)) {
                showTable();
            }
        }
    }
}

function deleteTable() {
    // on cache le tableau
    let tab_sorties = document.getElementById('tab_sorties');
    tab_sorties.style.display = "none";
    // on le remplace par un "aucun résultat" si il y est pas déjà
    if (document.getElementById('msgNotFound') == null) {
        let not_found = document.createElement('h1');
        not_found.setAttribute("class", "text-center");
        not_found.setAttribute("id", "msgNotFound");
        var not_found_content = document.createTextNode('Aucun résultat trouvé !');
        not_found.appendChild(not_found_content);
        // ajoute le nouvel élément créé et son contenu dans le DOM
        document.getElementById('result').appendChild(not_found);
    }
}

function showTable() {
    // On supprime l'élement ajouté "Aucun résultat"
    if (document.getElementById('msgNotFound') != null) {
        let h1 = document.getElementById('msgNotFound');
        document.getElementById('result').removeChild(h1);
    }
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


function getTabSortiesNotHidden() {
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    let tab = [];
    for (let sortie of sorties_nom) {
        if (!(sorties_nom[i].parentNode.style.display = "none")) {
            tab.push(sorties_nom);
        }
    }
    return tab;
}