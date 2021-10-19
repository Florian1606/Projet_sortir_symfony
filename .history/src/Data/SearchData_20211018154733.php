<?php

namespace App\Data;

class SearchData
{

    /**
     *  @var string
     */
    public $q = '';

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

    /**
     * @var boolean
     */
    public $isSortiesOrganisateur = false;

    /**
     * @var boolean
     */
    public $isSortiesInscrit = false;

    /**
     * @var boolean
     */
    public $isSortiesNonInscrit = false;

    /**
     * @var boolean
     */
    public $isSortiesPassees = false;
}
