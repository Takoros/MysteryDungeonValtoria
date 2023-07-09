const { AttachmentBuilder, EmbedBuilder, SlashCommandBuilder, bold, italic } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");
const buttonPages = require("../functions/pagination.js");
const { typeToIcon, getSpeciesFileName, displayDescription } = require('../functions/displayTools.js');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('resume')
		.setDescription('Obtenez diverses information sur votre personnage.')
        .addStringOption(option =>
            option.setName('information')
                .setDescription("Quel genre d'information voulez-vous obtenir ?")
                .setRequired(true)
                .addChoices(
                    { name: 'Personnage', value: 'character' },
                    { name: 'Rotations', value: 'rotations' },
                    { name: 'Attaques Disponibles', value: 'available-attacks' }
                )
        ),
	async execute(interaction) {
        resumeType = interaction.options.getString('information');

        if(resumeType === 'character'){
            resumeCharacter(interaction);
        }
        else if(resumeType === 'rotations'){
            resumeRotations(interaction);
        }
        else if(resumeType === 'available-attacks'){
            resumeAvailableAttacks(interaction)
        }
	},
};

async function resumeCharacter(interaction){
    var api_data = new Object();
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/character/resume",
        api_data,
        interaction
    )

    await api_call.connectToAPI()
    data = api_call.getAPIResponseData();

    if(api_call.getAPIResponseCode() === 200){
        let speciesFileName = getSpeciesFileName(data.get('species'), data.get('isShiny'))+'.png';
        const speciesIcon = new AttachmentBuilder('./assets/pokemon-icons/'+speciesFileName);
        const resumeEmbed = new EmbedBuilder().setAuthor({ name: `Fiche de ${data.get('name')} (${data.get('species')}, ${data.get('gender')}, ${data.get('age')} ans)`})
                                            .setDescription(bold(`:star: Niveau :`)+` ${data.get('level')} (EXP: ${data.get('xp')} / ${data.get('nextLevelXP')}) \n`
                                                            + bold(`:beginner: Rang :`)+ ' Cuivre \n'
                                                            + `${bold('Description :')} ${displayDescription(data.get('description'))}`
                                                            )
                                            .setThumbnail('attachment://'+speciesFileName);

        resumeEmbed.addFields(
            {name: `\u200B`, value: '---------------------', inline: true},
            {name: bold('        :book: STATS :book:'), value: '-----------------------', inline: true},
            {name: `\u200B`, value: '---------------------', inline: true},
            {name: `:heart: Vitalité : ${data.get('stats').vitality}`, value: bold(`<:presenceIcon:1122309757651386458> Présence : ${data.get('stats').presence}`), inline: true},
            {name: `<:strengthIcon:1122307739360706680> Force : ${data.get('stats').strength}`, value: bold(`:shield: Endurance : ${data.get('stats').stamina}`), inline: true},
            {name: `:crystal_ball: Pouvoir : ${data.get('stats').power}`, value: bold(`<:braveryIcon:1122311152924373102> Courage : ${data.get('stats').bravery}`), inline: true},
            {name: `<:impassivenessIcon:1122312380811071579> Impassibilité : ${data.get('stats').impassiveness}`, value: bold(`<:speedIcon:1122313250948784208> Vitesse : ${data.get('stats').speed}`), inline: true},
            {name: `<:coordinationIcon:1122313205419622460> Coordination : ${data.get('stats').coordination}`, value: bold(`:star: Point d'Action : ${data.get('stats').actionPoint}`), inline: true},
            {name: `<:agilityIcon:1122313808015265854> Agilité : ${data.get('stats').agility}`, value: bold(`:dna: Point de Stat: ${data.get('statPoints')}`), inline: true},
        )
        interaction.reply({ embeds: [resumeEmbed], files: [speciesIcon]});
    }
}

async function resumeRotations(interaction){
    var api_data = new Object();
    api_data.discordUserId = interaction.user.id;
    api_data.rotationType = 'Rotation';

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/character/resume/rotation",
        api_data,
        interaction
    )

    await api_call.connectToAPI();
    rotationData = api_call.getAPIResponseData();
    rotationCallCode = api_call.getAPIResponseCode();

    if(rotationCallCode !== 200){
        return ;
    }

    var api_data = new Object();
    api_data.discordUserId = interaction.user.id;
    api_data.rotationType = 'Opener';

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/character/resume/rotation",
        api_data,
        interaction
    )

    await api_call.connectToAPI();
    openerData = api_call.getAPIResponseData();
    openerCallCode = api_call.getAPIResponseCode();

    if(rotationCallCode === 200 && openerCallCode === 200){
        const bookIcon = new AttachmentBuilder('./assets/book.png');
        const resumeEmbed = new EmbedBuilder().setAuthor({ name: 'Vos Rotations !', iconURL: 'attachment://book.png'});
        resumeEmbed.addFields({name: `Ordre`, value: `\u200B`, inline: true}, {name: `Ouverture`, value: `Vos 5 premières attaques.`, inline: true}, {name: `Rotation`, value: `Votre cycle d'attaques répétées.`, inline: true});
        
        const attacksOrderArray = ['attackOne', 'attackTwo', 'attackThree', 'attackFour', 'attackFive'];
        let i = 1;

        attacksOrderArray.forEach(attackName => {
            resumeEmbed.addFields({name: `${i}`, value: `\u200B`, inline: true},
                                  {name: `${typeToIcon(openerData.get(attackName).type)} ${openerData.get(attackName).name} (${openerData.get(attackName).actionPointCost} PA)`, value: `${openerData.get(attackName).description}`, inline: true},
                                  {name: `${typeToIcon(rotationData.get(attackName).type)} ${rotationData.get(attackName).name} (${rotationData.get(attackName).actionPointCost} PA)`, value: `${rotationData.get(attackName).description}`, inline: true});
            i++;
        });

        interaction.reply({ embeds: [resumeEmbed],  files: [bookIcon]});
    }
}

async function resumeAvailableAttacks(interaction){
    var api_data = new Object();
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/character/resume/available-attacks",
        api_data,
        interaction
    )

    await api_call.connectToAPI()
    
    if(api_call.getAPIResponseCode() === 200){
        data = api_call.getAPIResponseData();

        let allAttacksFieldsArray = [];

        data.forEach(attack => {
            let attackInfo = bold(`PA : ${attack.actionPointCost} \n Type : ${typeToIcon(attack.type)}`);
            let attackStat = bold(`[Puissance : ${attack.power}] [P.Status : ${attack.statusPower}] [P.Critique : ${attack.criticalPower}]`);
            let middleSeparator = '-------------------------------------------------------------------'

            let fieldOne = {name: `---------\u200B\n`+bold(`Lvl : ${attack.levelRequired}`), value: attackInfo, inline: true};
            let fieldTwo = {name: `${middleSeparator}\n${attack.name}`, value: `${attack.description} \n ${attackStat}`, inline: true};
            let fieldThree = {name: `\u200B\n\u200B`, value: `\u200B`, inline: true};

            allAttacksFieldsArray.push([fieldOne, fieldTwo, fieldThree]);
        });

        embedArray = [];
        while (allAttacksFieldsArray.length > 0) {
            const embed = new EmbedBuilder().setAuthor({ name: `Attaques disponibles de votre Personnage`});
            
            for (let time = 1; time <= 5; time++) {
                if(!allAttacksFieldsArray.length < 1){
                    allAttacksFieldsArray[0].forEach(field => {
                        embed.addFields(field);
                    });

                    allAttacksFieldsArray.shift();
                }
            }

            embedArray.push(embed);
        }
        
        const pages = embedArray;
        buttonPages(interaction, pages);
    }
}
