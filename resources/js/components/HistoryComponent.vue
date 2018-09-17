<template>
  <div class="history">
    <h3>History</h3>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Winner</th>
          <th>Moves</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(h, index) in history">
          <td>{{ index + 1 }}</td>
          <td>{{ h.winner }}</td>
          <td v-html="parseMoves(h.moves)"></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  props: {
    history: Array
  },
  data: function () {
    return {}
  },
  methods: {
    parseMoves (data) {
      let arr = JSON.parse(data);
      let str = '';
      arr.forEach(function(item, i) {
        let index = i + 1;
        str += '<b>' + index + '</b>. ' + item[0] + ' - [' + item[1] + ']; ';
      });
      return str;
    }
  }
}
</script>