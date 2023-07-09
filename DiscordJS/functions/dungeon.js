const {
    ActionRowBuilder,
    ButtonBuilder,
    ButtonStyle,
    ComponentType,
} = require("discord.js");

const { CallingAPI } = require("./CallingAPI.js");
const generateDungeonImage = require("./generateDungeonImage.js");
const {displayCrownIfLeader, preparePokemonTypeDisplay} = require('./displayTools.js');

async function dungeonButtons(interaction, file, dungeonImageData, time = 120000) {
    if (!interaction) throw new Error("Please provide an interaction argument");
    if (typeof time !== "number") throw new Error("Time must be a number.");
    if (parseInt(time) < 30000) throw new Error("Time must be greater than 30 Seconds");

    if(dungeonImageData.instanceStatus === 'dungeon_status_preparation'){
        displayPreparationDungeon(interaction, file, dungeonImageData, time);
    }
    else if (dungeonImageData.instanceStatus === 'dungeon_status_exploration'){
        displayExplorationDungeon(interaction, file, dungeonImageData, time);
    }
    else if (dungeonImageData.instanceStatus === 'dungeon_status_termination'){
        displayTerminationDungeon(interaction, file, dungeonImageData, time);
    }
}

async function displayPreparationDungeon(interaction, file, dungeonImageData, time){
    const link = new ButtonBuilder()
    .setLabel('Version Web')
    .setURL(dungeonImageData.webLink)
    .setStyle(ButtonStyle.Link);

    const enterDungeonButton = new ButtonBuilder()
        .setCustomId("enterDungeon")
        .setEmoji("🚪")
        .setStyle(ButtonStyle.Success);

    const leaveDungeonButton = new ButtonBuilder()
        .setCustomId("leaveDungeon")
        .setEmoji("🚪")
        .setStyle(ButtonStyle.Danger);

    const preparationButtonRow = new ActionRowBuilder().addComponents(link, enterDungeonButton, leaveDungeonButton);
    
    let membersData = await getDungeonMembers(interaction);
    let ExplorerList = membersData['explorerList'];
    let ExplorerListContent = '';

    ExplorerList.forEach(explorer => {
        ExplorerListContent += `- [Niv.${explorer['level']}] ${displayCrownIfLeader(explorer['isLeader'])} ${explorer['name']} (${preparePokemonTypeDisplay(explorer['type'])} ${explorer['species']}, ${explorer['gender']}) \n`
    });

    messageContent = `Préparation avant d'entrer dans le donjon.\n\nMembres : (${ExplorerList.length}/4)\n` + ExplorerListContent;
    response = await interaction.editReply({
        content: messageContent,
        components: [preparationButtonRow],
        fetchReply: true
    });

    collector = await response.createMessageComponentCollector({
        componentType: ComponentType.Button,
        time,
    });

    collector.on("collect", async (i) => {
        if (i.user.id !== interaction.user.id){
            return i.reply({
                content: "Vous ne pouvez pas utiliser ces boutons.",
                ephemeral: true,
            });
        }

        if (i.customId === "enterDungeon") {
            enterResponse = await enterDungeon(i, interaction);

            if(enterResponse.code === 400){
                if(enterResponse.message){
                    await i.reply({
                        content: enterResponse.message,
                        ephemeral: true
                    });
                }
                else {
                    await i.reply({
                        content: "\nErreur, veuillez réessayer plus tard",
                        ephemeral: true
                    });
                }

                collector.resetTimer();
                return;
            }

            displayExplorationDungeon(interaction, file, dungeonImageData, time, response);
            collector.stop();
        }
        else if (i.customId === "leaveDungeon"){
            leaveResponse = await leaveDungeon(i, interaction);

            if(leaveResponse.code === 400){
                if(leaveResponse.message){
                    await i.reply({
                        content: leaveResponse.message,
                        ephemeral: true
                    });
                }
                else {
                    await i.reply({
                        content: 'Erreur, veuillez réssayer plus tard.',
                        ephemeral: true
                    });
                }
    
                return;
            }

            await i.reply({
                content: 'Vous avez bien quitté le donjon.',
                ephemeral: true
            });

            collector.stop();
            return ;
        }
    });

    collector.on("end", async (i) => {
        response.fetch().then(function() {
            response.delete();
        }).catch(error => {
            console.log(error);
        });
    });

    return ; 
}

async function displayExplorationDungeon(interaction, file, dungeonImageData, time, response = null){
    // adding buttons
    const left = new ButtonBuilder()
        .setCustomId("left")
        .setEmoji("◀️")
        .setStyle(ButtonStyle.Primary);

    const up = new ButtonBuilder()
        .setCustomId("up")
        .setEmoji("🔼")
        .setStyle(ButtonStyle.Primary);

    const down = new ButtonBuilder()
        .setCustomId("down")
        .setEmoji("🔽")
        .setStyle(ButtonStyle.Primary);

    const right = new ButtonBuilder()
        .setCustomId("right")
        .setEmoji("▶️")
        .setStyle(ButtonStyle.Primary);

    const leave = new ButtonBuilder()
        .setCustomId("leave")
        .setEmoji("🚪")
        .setStyle(ButtonStyle.Danger);

    const fight = new ButtonBuilder()
        .setCustomId("fight")
        .setEmoji("⚔")
        .setStyle(ButtonStyle.Primary);

    const link = new ButtonBuilder()
        .setLabel('Version Web')
        .setURL(dungeonImageData.webLink)
        .setStyle(ButtonStyle.Link);

    const interact = new ButtonBuilder()
        .setCustomId("interact")
        .setEmoji("🤚")
        .setStyle(ButtonStyle.Primary);

    const buttonRow = new ActionRowBuilder().addComponents(left, up, down, right, fight);
    const moreButtonsRow = new ActionRowBuilder().addComponents(link, leave, interact);

    if(response === null){
        response = await interaction.editReply({
            files: [file],
            components: [buttonRow, moreButtonsRow],
            fetchReply: true
        });
    }
    else {
        response = await interaction.followUp({
            files: [file],
            components: [buttonRow, moreButtonsRow],
            fetchReply: true
        });
    }

    collector = await response.createMessageComponentCollector({
        componentType: ComponentType.Button,
        time,
    });

    collector.on("collect", async (i) => {
        if (i.user.id !== interaction.user.id){
            return i.reply({
                content: "You can't use these buttons",
                ephemeral: true,
            });
        }

        if (i.customId === "leave") {
            leaveResponse = await leaveDungeon(i, interaction);

            if(leaveResponse.code === 400){
                if(leaveResponse.message){
                    await response.edit({
                        content: leaveResponse.message,
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                else {
                    await response.edit({
                        content: 'Erreur, veuillez réssayer plus tard.',
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                 await i.update({fetchreply:false});
                return;
            }
            await i.editReply('');
            await response.edit({
                content: 'Vous avez bien quitté le donjon.',
                files: [],
                components: []
            });

            return ;
        }
        else if(i.customId === "fight"){
            fightResponse = await fightMonsters(i, interaction);

            if(fightResponse.code === 400){
                if(fightResponse.message){
                    await response.edit({
                        content: fightResponse.message,
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                else {
                    await response.edit({
                        content: 'Erreur, veuillez réssayer plus tard.',
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                await i.update({fetchreply:false});
                return;
            }

            if(fightResponse.victory === false){
                i.reply({
                    content: 'Les pokémons sauvages ont vaincu votre équipe, vous vous êtes enfui en courant du donjon.\nRapport de combat : '+fightResponse.combatLogUrl,
                    ephemeral: true
                })
                displayTerminationDungeon(interaction, file, dungeonImageData, time, response);
                collector.stop();

                return;
            }

            i.reply({
                content: "Votre équipe à vaincu les pokémons sauvages qui s'étaient mis sur votre chemin.\nRapport de combat : "+fightResponse.combatLogUrl,
                ephemeral: true
            });

            dungeonImageData = await generateDungeonImage(i);
            file = dungeonImageData['image'];
            
            await response.edit({
                content: '',
                files: [file],
                components: [buttonRow, moreButtonsRow],
            });

            collector.resetTimer();
            return;
        }
        else if(i.customId === "up" || i.customId === "down" || i.customId === "right" || i.customId === "left"){
            moveResponse = await moveExplorers(i, interaction);

            if(moveResponse.code === 400){
                if(moveResponse.message){
                    await response.edit({
                        content: moveResponse.message,
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                else {
                    await response.edit({
                        content: 'Erreur, veuillez réssayer plus tard.',
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                await i.update({fetchReply:false});
                return;
            }
        }
        else if(i.customId === 'interact'){
            interactResponse = await interactWithDungeon(i, interaction);

            if(interactResponse.code === 400){
                if(interactResponse.message){
                    await response.edit({
                        content: interactResponse.message,
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                else {
                    await response.edit({
                        content: 'Erreur, veuillez réssayer plus tard.',
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                await i.update({fetchReply:false});
                return;
            }

            displayTerminationDungeon(interaction, file, dungeonImageData, time, response);
            collector.stop();
            
            return;
        }

        await i.deferUpdate();
        dungeonImageData = await generateDungeonImage(i);
        file = dungeonImageData['image'];
        
        await response.edit({
            content: '',
            files: [file],
            components: [buttonRow, moreButtonsRow],
        });
        
        await i.editReply({fetchReply: false});
        collector.resetTimer();
    });

    // ending the collector
    collector.on("end", async (i) => {
        response.fetch().then(function() {
            response.delete();
        }).catch(error => {
            console.log(error);
        });
    });
}

async function displayTerminationDungeon(interaction, file, dungeonImageData, time, response = null){
    const link = new ButtonBuilder()
    .setLabel('Version Web')
    .setURL(dungeonImageData.webLink)
    .setStyle(ButtonStyle.Link);

    const leave = new ButtonBuilder()
    .setCustomId("leave")
    .setEmoji("🚪")
    .setStyle(ButtonStyle.Danger);

    const terminationButtonsRow = new ActionRowBuilder().addComponents(link, leave);

    if(response === null){
        response = await interaction.editReply({
            content: 'Donjon terminé.',
            files: [file],
            components: [terminationButtonsRow],
            fetchReply: true
        });
    }
    else {
        response = await interaction.followUp({
            content: 'Donjon terminé.',
            files: [file],
            components: [terminationButtonsRow],
            fetchReply: true
        });
    }

    collector = await response.createMessageComponentCollector({
        componentType: ComponentType.Button,
        time,
    });

    collector.on("collect", async (i) => {
        if (i.user.id !== interaction.user.id){
            return i.reply({
                content: "You can't use these buttons",
                ephemeral: true,
            });
        }
        await i.deferUpdate();

        if (i.customId === "leave") {
            leaveResponse = await leaveDungeon(i, interaction);

            if(leaveResponse.code === 400){
                if(leaveResponse.message){
                    await response.edit({
                        content: 'Donjon terminé.\n'+leaveResponse.message,
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
                else {
                    await response.edit({
                        content: 'Donjon terminé. \nErreur, veuillez réssayer plus tard.',
                        files: [file],
                        components: [buttonRow, moreButtonsRow],
                    });
                }
    
                return;
            }

            await response.edit({
                content: 'Vous avez bien quitté le donjon.',
                files: [],
                components: []
            });

            return ;
        }

        await response.edit({
            content: '',
            files: [file],
            components: [terminationButtonsRow],
        });

        collector.resetTimer();
    });

    // ending the collector
    collector.on("end", async (i) => {
        response.fetch().then(function() {
            response.delete();
        }).catch(error => {
            console.log(error);
        });
    });
}

async function moveExplorers(i, interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;
    api_data.direction = i.customId;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/move",
        api_data
    )
    
    try {
        await api_call.connectToAPI();
        data = api_call.getAPIResponseData();

        return {
            'code' : api_call.getAPIResponseCode(),
            'message' : data.get('message'),
        };
    } catch (error) {
        console.log(error);

        return {
            'code' : api_call.getAPIResponseCode()
        };
    }
}

async function fightMonsters(i, interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/fight",
        api_data
    )

    try {
        await api_call.connectToAPI();
        data = api_call.getAPIResponseData();

        return {
            'code' : api_call.getAPIResponseCode(),
            'message' : data.get('message'),
            'victory' : data.get('victory'),
            'combatLogUrl' : data.get('combatLogUrl')
        };
    } catch (error) {
        console.log(error);

        return {
            'code' : api_call.getAPIResponseCode()
        };
    }
}

async function interactWithDungeon(i, interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/interact",
        api_data
    )
    
    try {
        await api_call.connectToAPI();
        data = api_call.getAPIResponseData();

        return {
            'code' : api_call.getAPIResponseCode(),
            'flavourText' : data.get('flavourText'),
        };
    } catch (error) {
        console.log(error);

        return {
            'code' : api_call.getAPIResponseCode()
        };
    }
}

async function leaveDungeon(i, interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/leave",
        api_data
    )
    
    try {
        await api_call.connectToAPI();
        data = api_call.getAPIResponseData();

        return {
            'code' : api_call.getAPIResponseCode(),
            'message' : data.get('message'),
        };
    } catch (error) {
        console.log(error);

        return {
            'code' : api_call.getAPIResponseCode()
        };
    }
}

async function enterDungeon(i, interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/enter",
        api_data
    )
    
    try {
        await api_call.connectToAPI();
        data = api_call.getAPIResponseData();

        return {
            'code' : api_call.getAPIResponseCode(),
            'message' : data.get('message'),
        };
    } catch (error) {
        console.log(error);

        return {
            'code' : api_call.getAPIResponseCode()
        };
    }
}

async function getDungeonMembers(interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/members",
        api_data
    )
    
    try {
        await api_call.connectToAPI();
        data = api_call.getAPIResponseData();

        return {
            'code' : api_call.getAPIResponseCode(),
            'explorerList' : data.get('explorerList'),
        };
    } catch (error) {
        console.log(error);

        return {
            'code' : api_call.getAPIResponseCode()
        };
    }
}



module.exports = dungeonButtons;