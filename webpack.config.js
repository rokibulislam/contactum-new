const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const devMode = process.env.NODE_ENV !== 'production';

module.exports = [
  // JS build
  {
    entry: {
      integrations: './src/admin/integrations.js',
      addon: './src/admin/modules.js',
      tools: './src/admin/tools.js',
      entries: './src/admin/entries.js',
      payments: './src/admin/payments.js',
      settings: './src/admin/settings.js',
      forms: './src/admin/forms.js',
      analytics: './src/admin/analytics.js',
    },

    mode: devMode ? 'development' : 'production',

    output: {
      path: path.resolve(__dirname, './assets/js'),
      filename: devMode ? '[name].js' : '[name].min.js',
    },

    externals: {
      jquery: 'jQuery',
      lodash: {
        commonjs: 'lodash',
        amd: 'lodash',
        root: '_',
      },
    },

    resolve: {
      alias: {
        vue$: 'vue/dist/vue.esm.js',
        '@': path.resolve(__dirname, './src/'),
        admin: path.resolve(__dirname, './src/admin/'),
      },
      modules: [
        path.resolve(__dirname, './node_modules'),
        path.resolve(__dirname, './src/'),
      ],
      extensions: ['.js', '.vue', '.json'],
    },

    // module: {
    //   rules: [
    //     {
    //       test: /\.vue$/,
    //       loader: 'vue-loader',
    //     },
    //     {
    //       test: /\.js$/,
    //       use: 'babel-loader',
    //       exclude: /node_modules/,
    //     },
    //     {
    //       test: /\.css$/i,
    //       use: ['style-loader', 'css-loader'],
    //     },
    //     {
    //       test: /\.svg$/i,
    //       use: 'svg-loader',
    //     },
    //   ],
    // },

        module: {
      rules: [
        {
          test: /\.vue$/,
          loader: 'vue-loader',
        },
        {
          test: /\.js$/,
          use: 'babel-loader',
          exclude: /node_modules/,
        },
        {
          test: /\.css$/i,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.scss$/i,
          use: ['vue-style-loader', 'css-loader', 'sass-loader'],
        },
        {
          test: /\.sass$/i,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'sass-loader',
              options: {
                sassOptions: {
                  indentedSyntax: true,
                },
              },
            },
          ],
        },
        {
          test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/i,
          loader: 'file-loader',
          options: {
            name: '../fonts/[name].[ext]',
          },
        },
        {
          test: /\.svg$/i,
          loader: 'file-loader',
          options: {
            name: '../images/[name].[ext]',
          },
        },
      ],
    },
    
    plugins: [new VueLoaderPlugin()],

    devtool: devMode ? 'source-map' : false,
  },

  // CSS build
  {
    entry: {
      'contactum-admin': './src/styles/main.scss',
    },

    mode: devMode ? 'development' : 'production',

    output: {
      path: path.resolve(__dirname, './assets/js'),
      filename: devMode ? '[name].js' : '[name].min.js',
    },

    module: {
      rules: [
        {
          test: /\.css$/i,
          use: [MiniCssExtractPlugin.loader, 'css-loader'],
        },
        {
          test: /\.scss$/i,
          use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
        },
        {
          test: /\.sass$/i,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'sass-loader',
              options: {
                sassOptions: {
                  indentedSyntax: true,
                },
              },
            },
          ],
        },
        {
          test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/i,
          loader: 'file-loader',
          options: {
            name: '../fonts/[name].[hash:8].[ext]',
          },
        },
      ],
    },

    plugins: [
      new MiniCssExtractPlugin({
        filename: devMode
          ? '../css/contactum-admin.css'
          : '../css/contactum-admin.min.css',
      }),
    ],

    devtool: devMode ? 'source-map' : false,
  },
];