@extends('installer::backend.layouts.master')

@section('template_title')
    {{ trans('installer_messages.final.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-flag-checkered fa-fw" aria-hidden="true"></i>
    {{ trans('installer_messages.final.title') }}
@endsection


@section('container')

<h6 class="setup-title">Admin Login Credentials</h6>
<p class="text-center">The administrator account was created using the credentials supplied during setup.</p>
<p class="text-center">Store those credentials in an approved password manager before continuing.</p>

 <div class="setup-actions">


    <div class="buttons">
        <a href="{{ route('login') }}" class="button intsaller-btn" id="exit-admin-button" onclick="disableAdminButton()">
            {{ trans('installer_messages.final.admin_panel') }}
        </a>
    </div>

 </div>

    <script>
        function disableButton() {
            document.getElementById('exit-admin-button').classList.add('disabled');
            document.getElementById('exit-button').classList.add('disabled');
            document.getElementById('exit-button').innerText = 'Loading...';
        }

        function  disableAdminButton() {

            document.getElementById('exit-button').classList.add('disabled');

           document.getElementById('exit-admin-button').classList.add('disabled');
           document.getElementById('exit-admin-button').innerText = 'Loading...';
        }




    </script>


@endsection
