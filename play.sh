#!/usr/bin/env bash
# A wrapper around `php tictactoe.php` that queries the user for game options.
#
# Given the context of a command line game, using command line switches is an
# appropriate way of configuring the game, but isn't the most user-friendly.
# However, using this approach opens the door for enabling user prompting and
# input to be interactive via a wrapping shell script instead. (or even
# potentially an X-window or web browser GUI wrapper in the future.) This keeps
# the logic for game configuration well separated from the game logic itself.
#
# @TODO:
#    - Permit "quick defaults" so users can just press enter to skip past
#      each question instead of being forced to make a selection at each step.


# Set defaults.
PS3='> '


##
# Query the user for the given player type/difficulty. Will echo the
# appropriate command line switch based on the user input.
#
# Example:
#   P1_LEVEL="$(player_level 1)"
#
function player_level () {
    local PLAYER_OPTIONS=('Human' 'Easy CPU' 'Medium CPU' 'Hard CPU')
    local PLAYER_NUM="${1?'Provide a player number as the first argument'}"

    (>&2 echo '')
    (>&2 echo "Select Player ${PLAYER_NUM}: ")

    select OPT in "${PLAYER_OPTIONS[@]}"
    do
        case $REPLY in
            1)
                break  # Nothing special to do. Human player is the default.
                ;;
            2)
                echo "--p${PLAYER_NUM}-level 1"
                break
                ;;
            3)
                echo "--p${PLAYER_NUM}-level 2"
                break
                ;;
            4)
                echo "--p${PLAYER_NUM}-level 3"
                break
                ;;
            *)
                (>&2 echo 'Invalid option. Please select again.')
                ;;
        esac
    done
}

##
# Query the user for the character to use to represent the given player. Will
# echo the appropriate command line switch based on the user input.
#
# Example:
#   P1_MARK="$(player_mark 1)"
#
function player_mark () {
    local PLAYER_NUM="${1?'Provide a player number as the first argument'}"

    (>&2 echo '')

    INPUT='start out invalid'
    while [[ "${#INPUT}" -gt '1' ]]; do
        read -p "Enter a single character to use for Player ${PLAYER_NUM}: " INPUT
    done

    echo "--p${PLAYER_NUM}-mark ${INPUT}"
}


# Print title.
echo ''
echo 'Weclome to Tic-Tac-Toe'


# Call out to helper functions to set command line options.
P1_LEVEL="$(player_level 1)"
P2_LEVEL="$(player_level 2)"
P1_MARK="$(player_mark 1)"
P2_MARK="$(player_mark 2)"


# Run the game with the chosen options.
php tictactoe.php ${P1_LEVEL} ${P2_LEVEL} ${P1_MARK} ${P2_MARK}
