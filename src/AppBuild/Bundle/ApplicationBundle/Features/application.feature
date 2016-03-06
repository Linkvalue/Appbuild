Feature: application
  In order to manage applications
  As someone or something
  I need to be able to list, create, update and delete a application

  Scenario: List applications
    Given there is "1" application
    When I list all applications
    Then "1" applications should be displayed

  Scenario: Create a application
    When I add a application
    Then this application should have been saved

  Scenario: Update a application
    Given the application with "id" "1" exists
    Given the application with "id" "1000001" does not exist
    When I edit this application to have "id" "1000001"
    Then this application should have been saved

  Scenario: Delete a application
    Given the application with "id" "1" exists
    When I delete this application
    Then this application should have been removed
