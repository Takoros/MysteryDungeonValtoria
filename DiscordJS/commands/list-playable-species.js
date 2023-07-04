const { SlashCommandBuilder } = require('discord.js');
const { AttachmentBuilder, EmbedBuilder, italic } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");
const buttonPages = require('../functions/pagination.js');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('list-playable-species')
		.setDescription("Liste l'intégralité des espèces jouables."),
	async execute(interaction) {
		var api_data = new Object()
		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/data/list_species",
			api_data
		)
		await api_call.connectToAPI()
        if (api_call.getAPIResponseCode() !== 200) {
			console.log(`Code ${api_call.getAPIResponseCode()} : Error while performing request`);
            await interaction.reply('Erreur, veuillez réessayer plus tard.');
        }
        else {
			const pawIcon = new AttachmentBuilder('./assets/paw.png');
            const exampleEmbed = new EmbedBuilder().setAuthor({ name: 'Liste des espèces jouables', iconURL: 'attachment://paw.png'});

			let data = api_call.getAPIResponseData();
			let allSpeciesFieldsArray = [];

			data.forEach(species => {
                if(species.isPlayable){
                    let fieldOne = {name:`#${species.id}`,value:`\u200B`,inline: true};
                    let fieldTwo = {name:`${species.name} ${preparePokemonTypeDisplay(species.types)}`,value: italic(`${species.description}`),inline: true};
                    let fieldThree = {name:`\u200B`,value:`\u200B`,inline: true};
    
                    allSpeciesFieldsArray.push([fieldOne, fieldTwo, fieldThree]);
                }
			});

			embedArray = [];
			while (allSpeciesFieldsArray.length > 0) {
				const embed = new EmbedBuilder().setAuthor({ name: `Liste des espèces jouables`, iconURL:'attachment://paw.png'});
				
				for (let time = 1; time <= 5; time++) {
					if(!allSpeciesFieldsArray.length < 1){
						allSpeciesFieldsArray[0].forEach(field => {
							embed.addFields(field);
						});
	
						allSpeciesFieldsArray.shift();
					}
				}

				embedArray.push(embed);
			}
			const pages = embedArray;

            buttonPages(interaction, pages, 60000, [pawIcon]);
        }
	},
};

function preparePokemonTypeDisplay(types)
{
	typesNames = '';

	types.forEach(function(type, idx, array){
		if(idx === array.length - 1){
			typesNames += typeToIcon(type.name);
		}
		else {
			typesNames += typeToIcon(type.name)+' ';
		}
	});

	return typesNames;
}

function typeToIcon(type){
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
}