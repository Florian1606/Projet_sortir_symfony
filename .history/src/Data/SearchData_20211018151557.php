<?php

namespace App\Date;

class SearchData {

    /**
     *  @var string
     */
    public $q='';

    /**
     *  @var villes[]
     */
    public $villes = [];

    /**
     * @var null | date
     */
    public $dateDebut;
    /**
     * @var null | date
     */
    public $dateFin;


}