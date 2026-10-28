// Hubleto's webpack config with one fix: the react-ui chunk is matched in vendor/hubleto/react-ui too.
// Upstream matches only hubleto/src/react-ui/, so react-ui.js is not built and every app script fails.
import hubletoWebpackConfig from './vendor/hubleto/assets/webpack.config.js';

export default async (env) => {
  const config = await hubletoWebpackConfig(env);
  config.optimization.splitChunks.cacheGroups.reactUi.test = /[\\/]react-ui[\\/](?!node_modules)/;
  return config;
};
