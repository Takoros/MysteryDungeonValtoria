const { SlashCommandBuilder, ButtonBuilder, ButtonStyle, ActionRowBuilder, ComponentType } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('invite-dungeon')
		.setDescription("Permet d'inviter un joueur dans votre donjon, à condition que vous soyez le leader de celui-ci.")
        .addUserOption(option => option.setName('utilisateur').setDescription('Personnage à inviter.').setRequired(true)),
	async execute(interaction, time = 80000) {
        await interaction.deferReply();
        memberListData = await getDungeonMembersList(interaction);
        newMember = interaction.options.getUser('utilisateur');

		if(memberListData === 400){
			interaction.editReply({
				content: "Vous n'avez pas de groupe d'exploration de donjon.",
				ephemeral: true
			});
			return ;
		}

        if(memberListData && !isMemberInDungeon(memberListData.get('explorerList'), newMember.id)){
			await interaction.deleteReply();
			const response = await createInvite(newMember, interaction);

			const collector = await response.createMessageComponentCollector({
				componentType: ComponentType.Button,
				time,
			});

			collector.on("collect", async (i) => {
				if (i.user.id !== newMember.id){
					return i.reply({
						content: "Vous ne pouvez pas utiliser ces boutons.",
						ephemeral: true,
					});
				}

				if (i.customId === "accept") {
					let result = await checkEnterValidity(interaction, i, newMember.id);

					if(result === 400){
						collector.stop();
						return ;
					}
					else if(result === true){
						await joinDungeon(interaction, i, newMember.id);

						collector.stop();
						return ;
					}
					else {
						i.reply({ 
							content: 'Impossible, vous êtes déjà dans un donjon, ou vous ne vous êtes pas assez reposé.',
							ephemeral: true
						});

						interaction.user.send({
							content: `<@${newMember.id}> n'a pas pu accepter votre invitation, il doit se reposer, ou se trouve déjà dans un donjon.`
						});

						collector.stop();
					}
				}
				else if (i.customId === "refuse") {
					interaction.user.send({
						content: `<@${newMember.id}> à refusé votre invitation`
					});
					
					collector.stop();
				}
			});

			collector.on("end", async (i) => {
				await response.delete();
			});
        }
        else if(memberListData && isMemberInDungeon(memberListData.get('explorerList'), newMember.id)){
            interaction.editReply('Ce personnage est déjà dans le donjon.');
        }
		else {
			interaction.editReply({
				content: "Vous n'avez pas de groupe d'exploration de donjon.",
				ephemeral: true
			});
			return ;
		}
	},
};

async function createInvite(newMember, interaction){
	const refuseButton = new ButtonBuilder()
	.setCustomId("refuse")
	.setEmoji("❎")
	.setStyle(ButtonStyle.Danger);

	const acceptButton = new ButtonBuilder()
	.setCustomId("accept")
	.setEmoji("✅")
	.setStyle(ButtonStyle.Success);

	const buttonRow = new ActionRowBuilder().addComponents(refuseButton, acceptButton);

	response = await interaction.channel.send({
		content: `<@${newMember.id}>, <@${interaction.user.id}> vous invite à rejoindre son groupe d'exploration de donjon.`,
		components: [buttonRow],
		fetchReply: true
	});

	return response;
}

function isMemberInDungeon(memberList, newMemberId){
	let isInDungeon = false;

	memberList.forEach(explorer => {
		if(explorer['userId'] === newMemberId){
			isInDungeon = true;
		}
	});

    return isInDungeon;
}

async function getDungeonMembersList(interaction){
    var api_data = new Object()
	api_data.discordUserId = interaction.user.id;

	var api_call = new CallingAPI(
		interaction.client.env.get("api_host"),
		interaction.client.env.get("api_token"),
		"api/dungeon/instance/members",
		api_data
	)

	await api_call.connectToAPI();

	if (api_call.getAPIResponseCode() === 200) {
		return api_call.getAPIResponseData();
	}
	else {
		return 400;
	}
}

async function checkEnterValidity(interaction, i, memberId){
	var api_data = new Object()
	api_data.discordUserId = memberId;

	var api_call = new CallingAPI(
		interaction.client.env.get("api_host"),
		interaction.client.env.get("api_token"),
		"api/dungeon/check/enter-validity",
		api_data
	)

	await api_call.connectToAPI();

	if (api_call.getAPIResponseCode() === 200) {
		let result = api_call.getAPIResponseData().get('result');

		return result;
	}
	else {
		interaction.user.send({
			content: `L'invitation à échoué : ${api_call.getAPIResponseData().get('message')}`
		});

		i.reply({
			content : `L'invitation à échoué : ${api_call.getAPIResponseData().get('message')}`,
			ephemeral: true
		});

		return 400;
	}
}

async function joinDungeon(interaction, i, memberId){
	var api_data = new Object()
	api_data.discordUserId = memberId;
	api_data.leaderDiscordUserId = interaction.user.id;

	var api_call = new CallingAPI(
		interaction.client.env.get("api_host"),
		interaction.client.env.get("api_token"),
		"api/dungeon/instance/join",
		api_data
	)

	await api_call.connectToAPI();

	if (api_call.getAPIResponseCode() === 200) {
		i.reply({
			content: "Vous avez bien rejoint le groupe d'exploration de donjon.",
			ephemeral: true
		});

		interaction.user.send({
			content: `<@${memberId}> a rejoint votre groupe d'exploration de donjon.`
		});
	}
	else {
		interaction.user.send({
			content: `L'invitation à échoué : ${api_call.getAPIResponseData().get('message')}`
		});

		i.reply({
			content : `L'invitation à échoué : ${api_call.getAPIResponseData().get('message')}`,
			ephemeral: true
		});
		
		return 400;
	}
}

