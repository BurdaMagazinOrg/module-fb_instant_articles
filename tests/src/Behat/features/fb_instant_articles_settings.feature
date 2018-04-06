@javascript @api
Feature: Facebook Instant Articles Settings
  In order to manage Facebook Instant Articles
  As an authenticated user
  I need to be able to configure Facebook Instant Articles

  Scenario: Facebook Page ID and Article Style are required
    Given I am logged in as a user with the "administrator" role
    And I open the Facebook Instant Articles Settings form
    And I disable HTML 5 required validation on the fields:
      | field |
      | Facebook Page ID |
      | Article Style |
    Then I should not see the following error messages:
      | error messages |
      | Facebook Page ID field is required. |
      | Article Style field is required.    |

  Scenario: Fill in all settings
    Given I am logged in as a user with the "administrator" role
    And I open the Facebook Instant Articles Settings form
    And I fill in "Facebook Page ID" with "123456789"
    And I fill in "Article Style" with "default"
    And I select "Facebook Audience Network" from "Ad Type"
    And I fill in "Audience Network Placement ID" with "1234_"
    And I select "Large (300 x 250)" from "Ad Dimensions"
    And I fill in "Analytics Embed Code" with "test_analytics_embed_code"
    And I select "Error" from "Transformer logging level"
    And I fill in "Canonical URL override" with "http://example.com"
    And I press the "Save configuration" button
    Then I should see the success message "The configuration options have been saved."
    And the "Facebook Page ID" field should contain "123456789"
    And the "Article Style" field should contain "default"
    And the "Ad Type" field should contain "fban"
    And the "Audience Network Placement ID" field should contain "1234_"
    And the "Ad Dimensions" field should contain "300x250"
    And the "Analytics Embed Code" field should contain "test_analytics_embed_code"
    And the "Transformer logging level" field should contain "ERROR"
    And the "Canonical URL override" field should contain "http://example.com"

  Scenario: Invalid ads placement id:
    Given I am logged in as a user with the "administrator" role
    And I open the Facebook Instant Articles Settings form
    And I fill in "Facebook Page ID" with "123456789"
    And I fill in "Article Style" with "default"
    And I select "Facebook Audience Network" from "Ad Type"
    And I fill in "Audience Network Placement ID" with "invalid"
    And I press the "Save configuration" button
    Then I should see the following error messages:
      | error messages |
      | You must specify a valid Placement ID when using the Audience Network ad type. To find or set your placement id, you will need to go to your Audience Network account for Instant Articles. In the account, navigate to ‘Placements’ and create a ‘Placement of Banner’ type. You will only need one placement. |

  Scenario: Invalid ads source URL:
    Given I am logged in as a user with the "administrator" role
    And I open the Facebook Instant Articles Settings form
    And I fill in "Facebook Page ID" with "123456789"
    And I fill in "Article Style" with "default"
    And I select "Source URL" from "Ad Type"
    And I fill in "Ad Source URL" with "invalid"
    And I press the "Save configuration" button
    Then I should see the following error messages:
      | error messages |
      | You must specify a valid source URL for your Ads when using the Source URL ad type. |

  Scenario: Invalid ads embed code:
    Given I am logged in as a user with the "administrator" role
    And I open the Facebook Instant Articles Settings form
    And I fill in "Facebook Page ID" with "123456789"
    And I fill in "Article Style" with "default"
    And I select "Embed Code" from "Ad Type"
    And I press the "Save configuration" button
    Then I should see the following error messages:
      | error messages |
      | You must specify Embed Code for your Ads when using the Embed Code ad type. |
