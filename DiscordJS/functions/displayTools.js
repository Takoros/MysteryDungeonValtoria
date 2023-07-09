const { italic } = require('discord.js');

module.exports = {
    /**
     * Returns a discord icon displayable for a given Type.
     */
    typeToIcon: function(type){
        if(type === 'Aventurier'){
            return ':beginner:';
        }
        else if(type === 'Normal'){
            return '<:normalIcon:1122285416515653672>';
        }
        else if(type === 'Feu'){
            return '<:feuIcon:1122285205110145135>';
        }
        else if(type === 'Eau'){
            return '<:eauIcon:1122285608316981339>';
        }
        else if(type === 'Electrik'){
            return '<:electrikIcon:1122285088156176436>';
        }
        else if(type === 'Psy'){
            return '<:psyIcon:1122285487407775846>';
        }
        else if(type === 'Ténèbres'){
            return '<:tnbresIcon:1122284850955698248>';
        }
        else if(type === 'Fée'){
            return '<:feIcon:1122285149640470618>'
        }
        else if(type === 'Plante'){
            return '<:planteIcon:1122285320537374730>';
        }
        else if(type === 'Combat'){
            return '<:combatIcon:1122285180267270184>';
        }
        else if(type === 'Spectre'){
            return '<:spectreIcon:1122285274878185472>';
        }
        else if(type === 'Roche'){
            return '<:rocheIcon:1122285520777658409>';
        }
        else if(type === 'Glace'){
            return '<:glaceIcon:1122285387071623279>';
        }
        else if(type === 'Dragon'){
            return '<:dragonIcon:1122285056845676645>';
        }
        else if(type === 'Acier'){
            return '<:acierIcon:1122285581012054087>';
        }
        else if(type === 'Poison'){
            return '<:poisonIcon:1122285453098352742>';
        }
        else if(type === 'Vol'){
            return '<:volIcon:1122285240501665813>';
        }
        else if(type === 'Sol'){
            return '<:solIcon:1122285348643405984>';
        }
        else if(type === 'Insecte'){
            return '<:insecteIcon:1122284793372082200>';
        }
    },

    /** 
     * Returns multiple discord icons displayable for given Types.
     */
    preparePokemonTypeDisplay: function(types)
    {
        typesNames = '';

        types.forEach(function(type, idx, array){
            if(idx === array.length - 1){
                if(typeof type === "string"){
                    typesNames += module.exports.typeToIcon(type);
                }
                else {
                    typesNames += module.exports.typeToIcon(type.name);
                }
            }
            else {
                if(typeof type === "string"){
                    typesNames += module.exports.typeToIcon(type)+' ';
                }
                else {
                    typesNames += module.exports.typeToIcon(type.name)+' ';
                }
            }
        });

        return typesNames;
    },

    getSpeciesFileName: function (species, isShiny){
        species = species.toLowerCase();

        // French characters replacement
        species = species.replaceAll('é', 'e');
        species = species.replaceAll('â', 'e');
        species = species.replaceAll('è', 'e');

        if(isShiny === true){
            species += '_shiny';
        }

        return species;
    },

    displayDescription: function(description){
        if(description === ''){
            return italic("Ce personnage n'a pas encore de description..")
        }

        return description;
    },

    displayCrownIfLeader: function (isLeader){
        if(isLeader){
            return ':crown:';
        }

        return '';
    }
}