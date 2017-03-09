import 'script!jquery';

import './_foundation';
import './jquery.fine-uploader';

import './components/animation_appear';
import './components/form_login';
import './components/progress_button';
import './components/select';
import './components/form-error';
import './components/uploader';
import './components/input-password';



// Call the function

import { inputPackage } from './components/input-package';
import { selectFilter } from './components/select-filter';
import { checkPassword } from './components/input-password';

checkPassword();

const pageCreateApp = $('#create-app');
const pageCreateUser = $('#create-user');
const pageAccount = $('#account');

if (pageCreateApp.length > 0) {
  inputPackage();
};

if (pageCreateApp.length > 0 || pageCreateUser.length > 0) {
  selectFilter();
};

if (pageAccount.length > 0 || pageCreateUser.length > 0 || pageCreateApp.length > 0) {
  checkPassword();
};
