Feature: application
  In order to manage applications
  As someone or something
  I need to be able to list, create and update applications and application builds

  Background:
    Given I am authenticated with role "ROLE_SUPER_ADMIN"
    Given there are these applications:
      | app_id | app_label   | app_support |
      | 1      | android app | android     |
      | 2      | ios app     | ios         |
    Given there are these builds:
      | app_id | build_id | build_version | build_file     |
      | 1      | 1        | 1.0           | android_v3.apk |
      | 1      | 2        | 2.0           | android_v3.apk |
      | 2      | 3        | 1.0.0         | ios_v2.ipa     |

  Scenario: List applications
    When I list all applications
    Then "2" applications should be displayed
    And I should not see the build with version "1.0" for application with id "1"

  Scenario: Create an ios application
    When I add an application with support "ios" with label "my new ios app"
    Then the application with label "my new ios app" should have been saved

  Scenario: Create an android application
    When I add an application with support "android" with label "my new android app"
    Then the application with label "my new android app" should have been saved

  Scenario: Update an application
    When I edit the application with id "1" to have label "new label"
    Then the application with label "new label" should have been saved

  Scenario: List builds
    When I list all builds of application with id "1"
    Then "2" builds should be displayed

  Scenario: Create a build
    When I add a build for application with id "1" with version "3.0" and file "android_v3.apk"
    Then the build with version "3.0" should have been saved

  Scenario: Update a build
    When I edit the build with id "1" to have version "1.1"
    Then the build with version "1.1" should have been saved

  Scenario: Download an android build
    Given I add a build for application with id "1" with version "3.0" and file "android_v3.apk"
    When I download the latest build
    Then I receive the latest build

  Scenario: Download an ios build
    Given I add a build for application with id "2" with version "2.0.0" and file "ios_v2.ipa"
    When I download the latest build
    Then I receive the latest build
