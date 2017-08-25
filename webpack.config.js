const path = require('path');

module.exports = {
  entry: './public/js/general.js',
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, './public/js')
  },
  watch: true
};