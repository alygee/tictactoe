<template>
  <div class="main-wrapper">
    <div class="main-header">
      <table>
        <tr>
          <td id="X" :class="{ active: isActiveHuman }">
            <div>
              <svg aria-label="X" role="img" viewBox="0 0 128 128">
                <path class="hFJ9Ve" d="M16,16L112,112"></path>
                <path class="hFJ9Ve" d="M112,16L16,112"></path>
              </svg>
            </div>
          </td>
          <td class="interlayer"></td>
          <td id="O" :class="{ active: isActiveAi }">
            <div>
              <svg aria-label="O" role="img" viewBox="0 0 128 128">
                <path class="hFJ9Ve" d="M64,16A48,48 0 1,0 64,112A48,48 0 1,0 64,16"></path>
              </svg>
            </div>
          </td>
        </tr>
      </table>
      <p v-if="this.state == 0"> Начните игру, сделайте первый ход. </p>
      <p v-else-if="this.state == 1">
        Ходит&nbsp;
        <svg v-show="turnId == huPlayer" aria-label="X" role="img" viewBox="0 0 128 128">
          <path class="hFJ9Ve" d="M16,16L112,112"></path>
          <path class="hFJ9Ve" d="M112,16L16,112"></path>
        </svg>
        <svg v-show="turnId == aiPlayer" aria-label="O" role="img" viewBox="0 0 128 128">
          <path class="hFJ9Ve" d="M64,16A48,48 0 1,0 64,112A48,48 0 1,0 64,16"></path>
        </svg>
      </p>
      <p v-else> {{ status }} </p>
    </div>
    <div class="playing-field">
      <table>
        <tr v-for="(tr, row) in rows" :key="row">
          <td v-for="(td, col) in columns" :key="col" :class="[tr.style, td.style]" @click="handleClick(row, col)">
            <svg class="X" :ref="'X'+row+col" aria-label="X" role="img" viewBox="0 0 128 128" style="display: none;">
              <path class="hFJ9Ve" d="M16,16L112,112" style="stroke: rgb(84, 84, 84); stroke-dasharray: 135.764; stroke-dashoffset: 0;"></path>
              <path class="hFJ9Ve" d="M112,16L16,112" style="stroke: rgb(84, 84, 84); stroke-dasharray: 135.764; stroke-dashoffset: 0;"></path>
            </svg>
            <svg class="O" :ref="'O'+row+col" aria-label="O" role="img" viewBox="0 0 128 128" style="display: none;">
              <path class="hFJ9Ve" d="M64,16A48,48 0 1,0 64,112A48,48 0 1,0 64,16" style="stroke: rgb(242, 235, 211);"></path>
            </svg>
          </td>
        </tr>
      </table>
    </div>
    <div class="repeat-play" @click="clearBoard()">Начать заново</div>
  </div>
</template>

<script>
  const states = {
    notStarted: 0,
    inProcess: 1,
    humanWon: 2,
    aiWon: 3,
    draw: 4,
  };

  const statuses = {
    win: 'You win!',
    lose: 'You lose!',
    draw: 'Draw.'
  }

  export default {
    data: function () {
      return {
        huPlayer: 'X',
        aiPlayer: 'O',
        turnId: 'X',
        board: [1, 2, 3, 4, 5, 6, 7, 8, 9],
        rows: [ { style: 'top'}, { style: 'middle'}, { style: 'bottom'} ],
        columns: [ { style: 'left' }, { style: 'center' }, { style: 'right' } ],
        state: states.notStarted,
        status: '',
        history: []
      }
    },
    computed: {
      isActiveHuman () {
        return this.turnId === this.huPlayer;
      },
      isActiveAi () {
        return this.turnId === this.aiPlayer;
      }
    },
    methods: {
      clearBoard () {
        this.board    = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        this.turnId   = this.huPlayer;
        this.state    = states.notStarted;
        for (let i = 0; i < 3; i++) {
          for (let j = 0; j < 3; j++) {
            $(this.$refs[this.huPlayer+i+j]).hide();
            $(this.$refs[this.aiPlayer+i+j]).hide();
          }
        }
      },
      handleClick (row, col) {
        const me = this;

        if (me.state == states.notStarted) me.state = states.inProcess;
        if (me.state !== states.inProcess) return;

        if (me.huPlayer === me.turnId) {
          let emptyCells = me.emptyIndices();
          let index = row*3 + col;
          if (Number.isInteger(me.board[index])) {
            me.makeMove(row, col);
          }

          if (me.state === states.inProcess) {
            axios.post('/move', {
              board: me.board
            })
            .then(function ({ data }) {
              let index = data + 1;
              // console.log(data);
              row = Math.ceil(index/3) - 1;
              col = index - row*3 - 1;
              me.makeMove(row, col);
            })
            .catch(function (error) {
              console.error(error);
            });
          }
        }
      },
      makeMove(row, col) {
        this.history.push([ this.turnId, [row, col] ]);
        this.updateBoard(row, col, this.turnId);
        $(this.$refs[this.turnId+row+col]).show();
        this.turnId = this.turnId == this.huPlayer ? this.aiPlayer : this.huPlayer;
        this.checkWinner();
      },
      checkWinner () {
        let me = this;
        let winner = '';
        if (me.winning(me.board, me.huPlayer)) {
          me.status = statuses.win;
          me.state = states.humanWon;
          winner = 'Human';
        } else if (me.winning(me.board, me.aiPlayer)) {
          me.status = statuses.lose;
          me.state = states.aiWon;
          winner = 'Ai';
        } else if (me.emptyIndices().length === 0) {
          me.status = 'Ничья';
          me.state = states.draw;
          winner = 'Draw';
        }

        if (me.state !== states.inProcess) {
          axios.post('/save', {
            winner: winner,
            moves: me.history
          })
          .then(function ({ data }) {
            let html = "<tr><td>" + data.id +"</td><td>" + data.winner + "</td><td>" + me.parseMoves(me.history) + "</td></tr>";
            $(".history .table tbody").append(html);
            me.history = [];
          })
          .catch(function (error) {
            console.error(error);
          });
        }
      },
      parseMoves (data) {
        let str = '';
        data.forEach(function(item, i) {
          let index = i + 1;
          str += '<b>' + index + '</b>. ' + item[0] + ' - [' + item[1] + ']; ';
        });
        return str;
      },
      updateBoard (row, col, turnId) {
        let index = row*3 + col;
        this.board[index] = turnId;
      },
      emptyIndices () {
        return this.board.filter(s => s != "X" && s != "O");
      },
      // победные комбинации с учётом индексов
      winning(board, player) {
        if(
          (board[0] == player && board[1] == player && board[2] == player) ||
          (board[3] == player && board[4] == player && board[5] == player) ||
          (board[6] == player && board[7] == player && board[8] == player) ||
          (board[0] == player && board[3] == player && board[6] == player) ||
          (board[1] == player && board[4] == player && board[7] == player) ||
          (board[2] == player && board[5] == player && board[8] == player) ||
          (board[0] == player && board[4] == player && board[8] == player) ||
          (board[2] == player && board[4] == player && board[6] == player)
        ) {
          return true;
        } else {
          return false;
        }
      }
    }
  }
</script>
