Contributing
------------

We encourage collaboration with anyone interested. By contributing to this
 project, you agree to abide by the [Drupal Code of Conduct](https://www.drupal.org/dcoc).

Also note that this project uses issues on Drupal.org, but opts to use a GitHub
 PR workflow rather than a traditional patch workflow.

1. Ensure there is an issue in the Drupal.org
 [project issue queue](https://www.drupal.org/project/issues/fb_instant_articles)
 before working on a PR. If there is not a relevant issue,
 [create one](https://www.drupal.org/node/73179). If there is a relevant issue,
 [assign it to yourself](https://www.drupal.org/node/2172049) before working on
 it. Note that the issue queue is for project management, whereas comments on
 GitHub PRs should focus on discussion about the code itself.

2. Prepare to work on the code locally:

    ```bash
    # Fork, then clone the repo:
    git clone git@github.com:YOUR_USERNAME/module-fb_instant_articles.git
    cd module-fb_instant_articles
    # Check out the correct core branch you are contributing to.
    # Example: `7.x-2.x` or `8.x-2.x`:
    git checkout 7.x-2.x
    # Create and checkout a local topic branch for the issue you're working on,
    # following this naming convention: [issue-number]-[short-description].
    # Example: If the issue is at http://drupal.org/node/123456 is 123456.
    git checkout -b 123456-example-bug
    ```

3. Make your changes, and then commit with a messaging following Drupal's
 [commit message standards](https://www.drupal.org/node/52287). Note that we
 recommend contributors squash their PR commits into sensible chunks of
 additions, with two goals:
 - To make it clear why each change was added via the commit message.
 - In the case of a large PR, to make commits easier for later reverting (if
   needed) than one monolithic, squashed PR.

    ```bash
    # Review changes as you go.
    git add -p
    # Add any new files.
    git add NEW_FILE_NAME
    # Review staged work before committing.
    git diff --staged
    # If everything you plan to commit is staged, commit with d.o syntax:
    # Issue #[issue number] by [comma-separated usernames]: [Short summary of the change]
    git commit -m "Issue #123456 by YOUR_USERNAME: Fixed example bug."
    # Push your topic branch to your fork:
    git push 123456-example-bug
    ```

    To increase the chance that your pull request is accepted, you will want to
    familiarize yourself with Drupal's
    [Standards, security and best practices](https://www.drupal.org/node/360052),
    and especially Drupal's
    [coding standards](https://www.drupal.org/coding-standards) and
    [documentation standards](https://www.drupal.org/node/1354).

4. [Open a GitHub PR](https://help.github.com/articles/creating-a-pull-request)
 against the correct branch (example: `7.x-2.x` or `8.x-2.x`).

5. Mark your corresponding Drupal.org issue with the status
 [Needs review](https://www.drupal.org/issue-queue/status#needs-review) and
 unassign yourself, so someone else knows they can feel free to review. At this
 point, use the Drupal.org issue queue as normal.

