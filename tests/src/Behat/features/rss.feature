@javascript @api
Feature: Content that has been enabled for Facebook Instant Articles is exposed to Facebook Instant Article via an RSS feed
  In order to send content to Facebook Instant Articles
  As an authenticated user
  I need to be able to create and configure one or more content types to be distributed to Facebook Instant Articles via RSS

  Scenario: Verify the default FBIA view is available
    Given I am logged in as a user with the "administrator" role
    When I am at "/admin/structure/views"
    Then I should see the text "Facebook Instant Articles RSS"

  Scenario: Toggle Facebook Instant Article support on a content type
    Given I am logged in as a user with the "administrator" role
    And "article" content:
      | title        | body                                                                                            |
      | Test article | Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Proin eget tortor risus. |
    And I am at "/admin/structure/types/manage/article"
    When I click "Facebook Instant Articles"
    And I check the box "Enable Facebook Instant Articles"
    And I press "Save content type"
    And I visit "/admin/structure/types/manage/article/display"
    Then I should see the text "Facebook Instant Articles"
    And I visit "/instant-articles.rss"
    Then the response should contain "Test article"
    And I visit "/admin/structure/types/manage/article"
    And I click "Facebook Instant Articles"
    And I uncheck the box "Enable Facebook Instant Articles"
    And I press "Save content type"
    And I visit "/admin/structure/types/manage/article/display"
    Then I should not see the text "Facebook Instant Articles"
    And I visit "/instant-articles.rss"
    Then the response should not contain "Test article"

  Scenario: Create content that should show in the RSS feed
    Given I am logged in as a user with the "administrator" role
    And "article" content:
      | title        | body                                                                                            |
      | Test article | Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Proin eget tortor risus. |
    And I am at "/admin/structure/types/manage/article"
    And I click "Facebook Instant Articles"
    And I check the box "Enable Facebook Instant Articles"
    And I press "Save content type"
    And I am at "/instant-articles.rss"
    Then the response should contain "Test article"
