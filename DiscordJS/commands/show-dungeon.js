const { SlashCommandBuilder } = require('discord.js');
const dungeonButtons = require("../functions/dungeon.js");
const generateDungeonImage = require('../functions/generateDungeonImage.js');


module.exports = {
	data: new SlashCommandBuilder()
		.setName('show-dungeon')
		.setDescription('Affiche les informations du donjon dans lequel vous vous trouvez.'),
	async execute(interaction) {
        await interaction.deferReply();
        let dungeonImageData = await generateDungeonImage(interaction);

        if(dungeonImageData === null){
            interaction.editReply("Votre personnage ne fait pas parti d'un donjon, ou bien une erreur s'est produite.");
            return ;
        }

        dungeonButtons(interaction, dungeonImageData.image, dungeonImageData);
	},
};