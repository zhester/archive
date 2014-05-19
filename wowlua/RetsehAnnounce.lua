
-- Global variables.
RetsehAnnounce_Globals = {

	-- The time the MD was cast.
	sent = nil,

	-- The last time MD faded.
	lastFade = 0,

	-- Stores the name of the MD target.
	target = "",

	-- Duraction of the MD buff.
	duration = 30,

	-- Is the mod listening for events.
	isListening = false,

	-- Debug variable for dev testing.
	debug = "",

	-- Goofy expressions.
	express = {
		", lol",
		", ZOMG RUN!",
		", everything's gonna be fine.",
		", /e crosses fingers.",
		", be afraid.",
		", you are not prepared!",
		", wipe incoming.",
		", prepare to fail.",
		", lolwut",
		", ...",
		", that's what she said.",
		"  Brick, where did you get a hand grenade?",
		"  PC Load Letter?!?",
		"  Stop looking at me, swan!",
	},
};


-- Handles Chat Message Announcements
function RetsehAnnounce_Announce(faded)

	local group_size = math.max(GetNumPartyMembers(), GetNumRaidMembers());
	
	local msg = "";	
	
	if faded then
		if GetTime() - RetsehAnnounce_Globals.lastFade > 2 then
			msg = "Misdirect Charges Expired or Used";
			RetsehAnnounce_Globals.lastFade = GetTime();
		end
	elseif RetsehAnnounce_Globals.target and RetsehAnnounce_Globals.target ~= "" then
		local expindex = math.random(1, getn(RetsehAnnounce_Globals.express));
		msg = "Misdirecting to -=["
			.. RetsehAnnounce_Globals.target .. "]=-"
			.. RetsehAnnounce_Globals.express[expindex];
	end

	if group_size > 5 then
		SendChatMessage(msg, "RAID");
	elseif group_size > 1 then
		SendChatMessage(msg, "PARTY");
	end

end


-- Handles slash commands.
function RetsehAnnounce_SlashCommandHandler(cmd)
	local active = "Not Active";
	if RetsehAnnounce_Globals.isListening then
		active = "Active";
	end
	local expindex = math.random(1, getn(RetsehAnnounce_Globals.express));
	RetsehAnnounce_Print(
		"RetsehAnnounce Version 0.0 (" .. active .. ")"
		.. RetsehAnnounce_Globals.express[expindex]
	);
end


-- User feedback.
function RetsehAnnounce_Print(str)
	if(DEFAULT_CHAT_FRAME) then
		DEFAULT_CHAT_FRAME:AddMessage(str, 1.0, 1.0, 1.0, 1.0);
	end
end



-- Handle all triggered events.
function RetsehAnnounce_OnEvent()

	-- VARIABLES_LOADED event
	if event == "VARIABLES_LOADED" then
		-- Make sure this is a hunter.
		local lclass, eclass = UnitClass("player");
		if eclass == "HUNTER" then
			RetsehAnnounce_Globals.isListening = true;
		else
			RetsehAnnounce_Globals.isListening = false;
			UnregisterAllEvents();
		end

	-- UNIT_SPELLCAST_SENT event
	-- Check for when the MD spell is cast on a group member.
	elseif event == "UNIT_SPELLCAST_SENT" then
		if arg1 and arg1 == "player" then
			if arg2 and string.find(arg2, "Misdirection") then
				RetsehAnnounce_Globals.target = arg4;
				RetsehAnnounce_Globals.sent = GetTime();
			end
		end

	-- UNIT_SPELLCAST_SUCCEEDED event
	-- Check for when the MD spell lands.
	elseif event == "UNIT_SPELLCAST_SUCCEEDED" then
		if arg1 and arg1 == "player" then
			if arg2 and string.find(arg2, "Misdirection") then
				if RetsehAnnounce_Globals.sent and GetTime() - RetsehAnnounce_Globals.sent < RetsehAnnounce_Globals.duration then
					RetsehAnnounce_Announce(false);
				end
				RetsehAnnounce_Globals.sent = nil;
				RetsehAnnounce_Globals.target = "";
			end
		end

	-- COMBAT_LOG_EVENT_UNFILTERED event
	-- Check for when the MD buff fades.
	elseif event == "COMBAT_LOG_EVENT_UNFILTERED" then		
		if arg7 and arg10 and arg2 and string.find(arg10, "Misdirection") and arg2 == "SPELL_AURA_REMOVED" and arg7 == UnitName("player") then
			RetsehAnnounce_Announce(true);
		end
	end
end


-- Run when the mod is first loaded.
function RetsehAnnounce_OnLoad()

	-- Set up slash command... in case I ever need it?
	SlashCmdList["RetsehAnnounce"] = RetsehAnnounce_SlashCommandHandler;
	SLASH_RetsehAnnounce1 = "/retsehannounce";

	-- Attach to important log events to trigger announcements.
	this:RegisterEvent("VARIABLES_LOADED");
	this:RegisterEvent("UNIT_SPELLCAST_SENT");
	this:RegisterEvent("UNIT_SPELLCAST_SUCCEEDED");
	this:RegisterEvent("COMBAT_LOG_EVENT_UNFILTERED");
end
