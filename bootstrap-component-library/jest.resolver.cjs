const path = require('path');

/**
 * Custom resolver.
 *  • Try Jest’s own resolver first ( available as options.defaultResolver ).
 *  • If that fails **and** the request starts with "@openeuropa/", fall back to
 *    the matching folder inside  src/components/  or  src/compositions/.
 */
module.exports = (request, options) => {
  try {
    return options.defaultResolver(request, options);
  } catch (err) {
    if (!request.startsWith('@openeuropa/')) throw err;

    const [, pkg, sub = ''] = request.match(/^@openeuropa\/([^/]+)\/?(.*)$/);

    for (const scope of ['components', 'compositions']) {
      const guess = path.join(options.rootDir, 'src', scope, pkg, sub);
      try {
        return options.defaultResolver(guess, options);
      } catch {}
    }

    throw err;
  }
};
