@extends('adminmodule::layouts.master')

@section('title', translate('Add_New_Customer'))

@section('content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <h2 class="fs-22 text-capitalize">{{translate('add_customer')}}</h2>
            </div>

            <form action="{{route('admin.customer.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row gy-4">
                            <div class="col-lg-8">
                                <h5 class="text-primary text-uppercase mb-4">{{translate('general_info')}}</h5>

                                <div class="row align-items-end">
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="f_name"
                                                   class="mb-2 text-capitalize">{{translate('first_name')}}</label>
                                            <input type="text" value="{{old('first_name')}}" name="first_name"
                                                   id="f_name" class="form-control"
                                                   placeholder="{{translate('Ex: Maximilian')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="l_name" class="mb-2">{{translate('last_name')}}</label>
                                            <input type="text" value="{{old('last_name')}}" name="last_name" id="l_name"
                                                   class="form-control"
                                                   placeholder="{{translate('Ex: Schwarzmüller')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="p_email" class="mb-2">{{translate('email')}}</label>
                                            <input type="email" value="{{old('email')}}" name="email" id="p_email"
                                                   class="form-control"
                                                   placeholder="{{translate('Ex: company@company.com')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="identity_type"
                                                   class="mb-2">{{translate('identity_type')}}</label>
                                            <select name="identification_type" class="js-select text-capitalize"
                                                    id="identity_type">
                                                <option disabled selected>-- {{translate('select_identity_type')}}
                                                    --
                                                </option>
                                                <option value="passport">{{translate('passport')}}</option>
                                                <option value="nid">{{translate('NID')}}</option>
                                                <option
                                                    value="driving_license">{{translate('driving_license')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="identity_card_num"
                                                   class="mb-2">{{translate('identity_number')}}</label>
                                            <input type="text" value="{{old('identification_number')}}"
                                                   name="identification_number" id="identity_card_num"
                                                   class="form-control" placeholder="{{translate('Ex: 3032')}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex flex-column justify-content-around gap-3">
                                    <h5 class="text-center text-capitalize">{{ translate('customer_image') }}</h5>

                                    <div class="d-flex justify-content-center">
                                        <div class="upload-file auto profile-image-upload-file">
                                            <input type="file" name="profile_image" class="upload-file__input"
                                                   accept=".jpg, .jpeg, .png, .webp" required>
                                            <div
                                                class="upload-file__img border-gray d-flex justify-content-center align-items-center w-180 h-180 p-0">
                                                <div class="upload-file__textbox text-center">
                                                    <img width="34" height="34"
                                                         src="{{ asset('public/assets/admin-module/img/document-upload.png') }}"
                                                         alt="" class="svg">
                                                    <h6 class="mt-2 fw-semibold">
                                                        <span class="text-info">{{ translate('Click to upload') }}</span>
                                                        <br>
                                                        {{ translate('or drag and drop') }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file__img__img h-100" width="180" height="180"
                                                     loading="lazy" alt="">
                                            </div>
                                            <a href="javascript:void(0)" class="remove-img-icon d-none">
                                                <i class="tio-clear"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <p class="opacity-75 mx-auto max-w220">
                                        {{ translate('JPG, JPEG, PNG, WEBP. Less Than 1MB') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column justify-content-around gap-3">
                                    <div>
                                        <h5 class="text-capitalize mb-2">{{ translate('identity_card_image') }}
                                        </h5>
                                        <p class="opacity-75">
                                            {{ translate('JPG, JPEG, PNG, WEBP. Less Than 1MB') }}
                                        </p>
                                    </div>

                                    <div class="upload-file d-flex custom" id="multi_image_picker"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{translate('account_information')}}</h5>

                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="phone_number" class="mb-2">{{translate('phone')}}</label>
                                    <input type="tel" pattern="[0-9]{1,14}" value="{{ old('phone') }}" id="phone_number"
                                           class="form-control w-100 text-dir-start"
                                           placeholder="{{translate('Ex: xxxxx xxxxxx')}}" required>
                                    <input type="hidden" id="phone_number-hidden-element" name="phone">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4 input-group_tooltip">
                                    <label for="password" class="mb-2">{{translate('password')}}</label>
                                    <input type="password" name="password" id="password" class="form-control"
                                           placeholder="{{translate('Ex: ********')}}" required>
                                    <i id="password-eye" class="mt-3 bi bi-eye-slash-fill text-primary tooltip-icon"
                                       data-bs-toggle="tooltip" data-bs-title=""></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4 input-group_tooltip">
                                    <label for="confirm_password"
                                           class="mb-2">{{translate('confirm_password')}}</label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="form-control"
                                           placeholder="{{translate('Ex: ********')}}" required>
                                    <i id="conf-password-eye"
                                       class="mt-3 bi bi-eye-slash-fill text-primary tooltip-icon"
                                       data-bs-toggle="tooltip" data-bs-title=""></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{translate('Upload_Other_Documents')}}</h5>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex flex-wrap gap-3" id="selected-files-container1"></div>
                            <div id="input-data"></div>
                            <!-- Upload New Documents -->
                            <div class="upload-file file__input" id="file__input">
                                <input type="file" class="upload-file__input2" multiple="multiple"
                                >
                                <div class="upload-file__img2">
                                    <div class="upload-box rounded media gap-4 align-items-center p-4 px-lg-5">
                                        <i class="bi bi-cloud-arrow-up-fill fs-20"></i>
                                        <div class="media-body">
                                            <p class="text-muted mb-2 fs-12">{{ translate('upload') }}</p>
                                            <h6 class="fs-12 text-capitalize">{{ translate('file_or_image') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button class="btn btn-primary" type="submit">{{translate('save')}}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- End Main Content -->

@endsection

@push('script')
    <link href="{{ asset('public/assets/admin-module/css/intlTelInput.min.css') }}" rel="stylesheet"/>
    <script src="{{ asset('public/assets/admin-module/js/intlTelInput.min.js') }}"></script>
    <script src="{{asset('public/assets/admin-module/js/spartan-multi-image-picker.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/js/password.js')}}"></script>
    <script src="{{ asset('public/assets/admin-module/js/upload-files-create.js') }}"></script>
    <script>
        "use strict";
        initializePhoneInput("#phone_number", "#phone_number-hidden-element");
        document.querySelectorAll('.upload-file__input').forEach(function(input) {
            input.addEventListener('change', function(event) {
                var file = event.target.files[0];
                var card = event.target.closest('.upload-file');
                var textbox = card.querySelector('.upload-file__textbox');
                var imgElement = card.querySelector('.upload-file__img__img');

                var prevSrc = textbox.querySelector('img').src;

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        imgElement.src = e.target.result;

                        $(card).find('.remove-img-icon').removeClass('d-none');
                        textbox.style.display = 'none';
                        imgElement.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }

                // Remove image
                $(card).find('.remove-img-icon').on('click', function() {
                    $(card).find('.upload-file__input').val('');
                    $(card).find('.upload-file__img__img').attr('src', '');
                    textbox.querySelector('img').src = prevSrc
                    textbox.style.display = 'block';
                    imgElement.style.display = 'none';
                    $(card).find('.remove-img-icon').addClass('d-none');
                });

            });
        });
    </script>

@endpush
