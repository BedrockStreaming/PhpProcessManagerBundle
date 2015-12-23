# Contributing

First of all, **thank you** for contributing, **you are awesome**!

Here are few rules to follow for a easier code review before the maintainers accept and merge your request.

##### Rules development

- Run the test suite.
- Write (or update) unit tests.
- Write (or update) documentation.
- Write [commit messages that make sense](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html)

##### Rules Pull Request 
- [Rebase your branch](http://git-scm.com/book/en/Git-Branching-Rebasing) before submitting your Pull Request.
- [Squash your commits] to "clean" your pull request before merging (http://gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html).
- Write and good description which gives the context and/or explains why you are creating it.

**Thank you!**


### Install project to development

Get sources dependencies :

```
composer install
```

### Unit tests

Launch unit tests with Atoum

```
./bin/atoum
```

### Check style

Check style use [Coke](https://github.com/M6Web/Coke)

Start code analysis :

```
./bin/coke
```