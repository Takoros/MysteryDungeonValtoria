const { SlashCommandBuilder } = require('discord.js');
const { AttachmentBuilder, EmbedBuilder, italic } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");
const buttonPages = require('../functions/pagination.js');
const { preparePokemonTypeDisplay, typeToIcon } = require('../functions/displayTools.js');

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

        if (api_call.getAPIResponseCode() === 200) {
			const pawIcon = new AttachmentBuilder('./assets/paw.png');

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


