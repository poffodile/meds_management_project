
var protocol = $(location).attr('protocol');
var hostname = $(location).attr('hostname');
var path     = protocol+'//'+hostname;
var host     = path+'/socialcareitsolutions/';

//admin user  account
$('#add_user_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'User Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                    message: 'User Name can only consist of alphanumeric characters'
                },
                remote: 
                {
                   message: 'User Name already exists',
                   url: 'check_username_unique',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
               // },
               // remote: 
               // {
               //     message: 'Email already exists',
               //     url: 'check-user-email-exists',
               //     type: 'GET',
               //     delay: 2000     // Send Ajax request every 2 seconds
               // }
            }
        },
        "job_title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: "This field can only consist of 'A-Z,a-z,0-9 &-' characters"
                }
            }
        },
        // "access_level": 
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         }
        //     },
        //     regexp: 
        //     {
        //         regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
        //         message: 'This field can only consist of alphanumeric characters'
        //     }
        // },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "payroll": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "holiday_entitlement": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "date_of_joining":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                },

            }
        },
        "date_of_leaving":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "current_location":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "personal_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "banking_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        }/*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }  */              

    }  
});

$('#edit_user_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        // "user_name": 
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         },
        //         stringLength: 
        //         {
        //             min: 2,
        //             max: 30,
        //             message: 'User Name must between 2 to 30 characters'
        //         },
        //         regexp: 
        //         {
        //             regexp : /^[A-Z,a-z,0-9,_,#,@ ]+$/,
        //             message: 'User Name can only consist of alphanumeric characters'
        //         },
        //         remote:     
        //         {   
        //            message: 'User Name already exists',
        //            url    : '/scits/admin/users/edit/username_exists',
        //            type   : 'GET',
        //            delay  : 2000     // Send Ajax request every 2 seconds
        //         }
        //     }
        // },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }/*,
               remote: 
               {
                   message: 'Email already exists',
                   url: 'check-user-email-exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
               }*/
            }
        },
        "job_title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "access_level": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            }
        },
        "payroll": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            }
        },
        "holiday_entitlement": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            },
            stringLength: 
            {
                min: 1,
                max: 100,
                message: 'This field can contain between 1 to 100 characters'
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },
        "date_of_joining":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                },
                
            }
        },
        "date_of_leaving":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "current_location":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "personal_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "banking_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        }/*,
        "company_id[]":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/
    }  
});

//admin agent Account
//admin user  account
$('#add_agent_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'User Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                    message: 'User Name can only consist of alphanumeric characters'
                },
                remote: 
                {
                   message: 'User Name already exists',
                   url: 'check_username_unique',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
               // },
               // remote: 
               // {
               //     message: 'Email already exists',
               //     url: 'check-user-email-exists',
               //     type: 'GET',
               //     delay: 2000     // Send Ajax request every 2 seconds
               // }
            }
        },
        "job_title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: "This field can only consist of 'A-Z,a-z,0-9 &-' characters"
                }
            }
        },
        "home_id[]": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "payroll": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "holiday_entitlement": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "date_of_joining":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }/*,
                regexp: {
                    regexp: /^[0-9-]+$/,
                    message: 'Name can only consist of digits'   
                }*/
            }
        },
        "date_of_leaving":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 10,
                    max: 10,
                    message: 'Please enter valid input'
                },
                regexp: {
                    regexp: /^[0-9-]+$/,
                    message: 'Name can only consist of digits'   
                }
            }
        },
        "current_location":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "personal_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "banking_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        }/*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }  */              

    }  
});

$('#edit_agent_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        // "user_name": 
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         },
        //         stringLength: 
        //         {
        //             min: 2,
        //             max: 30,
        //             message: 'User Name must between 2 to 30 characters'
        //         },
        //         regexp: 
        //         {
        //             regexp : /^[A-Z,a-z,0-9,_,#,@ ]+$/,
        //             message: 'User Name can only consist of alphanumeric characters'
        //         },
        //         remote:     
        //         {   
        //            message: 'User Name already exists',
        //            url    : '/scits/admin/users/edit/username_exists',
        //            type   : 'GET',
        //            delay  : 2000     // Send Ajax request every 2 seconds
        //         }
        //     }
        // },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }/*,
               remote: 
               {
                   message: 'Email already exists',
                   url: 'check-user-email-exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
               }*/
            }
        },
        "job_title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "home_id[]": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            }
        },
        "payroll": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            }
        },
        "holiday_entitlement": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            },
            stringLength: 
            {
                min: 1,
                max: 100,
                message: 'This field can contain between 1 to 100 characters'
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },
        "date_of_joining":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                },
                
            }
        },
        "date_of_leaving":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "current_location":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "personal_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "banking_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        }/*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/

    }  
});
/* Childs */
$('#add_service_user_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Username must be between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                    message: 'User Name can only consist of alphanumeric characters'
                },
                remote: 
                {
                   message: 'User Name already exists',
                   url: 'check_username_exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
                }
            }
        },  
        "admission_number": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Admission Number  can only consist of characters'
                }
            }
        },
         "section": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }   ,
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Section Name can only consist of characters'
                }
            }
        },
        "local_authority": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: {
                    min: 2,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                   regexp: /^[A-Za-z]+$/,
                    message: 'Local Authority can only contain letters (A-Z or a-z)'
                }
            }
        },
        "height": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 20,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Height  can only consist of characters'
                }
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "mobile": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Mobile No. must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Mobile No can only consist of digits'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
                emailAddress: 
                {
                  message: 'The value is not a valid email address'
                },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               },
               remote: 
               {
                   message: 'Email already exists',
                   url: 'check-serviceuser-email-exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
               }
            }
        },
    
        "weight": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Height  can only consist of characters'
                }
            }
        },

        "hair_and_eyes": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 100,
                    message: 'This field must between 1 to 50 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },

        "markings": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 100,
                    message: 'This field must between 1 to 100 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of characters'
                }
            }
        },
        "short_description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                /*stringLength: 
                {
                    min: 2,
                    max: 200,
                    message: 'This field must between 2 to 200 characters'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },
       /* "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },*/
        "personal_info": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "education_history": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "bereavement_issues": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "drug_n_alcohol_issues": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "mental_health_issues": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        }

    }  
});

$('#edit_service_user_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "admission_number": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Admission Number  can only consist of characters'
                }
            }
        },
         "section": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Section Name can only consist of alphanumeric characters'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               /*notEmpty: 
               {
                  message: 'This field is required'
               },*/
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }/*,
               remote: 
               {
                   message: 'Email already exists',
                   url: 'check-user-email-exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
               }*/
            }
        },
         "height": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Height  can only consist of characters'
                }
            }
        },
        
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "mobile": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                    message: 'This field is required'
                },*/
                /*stringLength: 
                {
                    min: 8,
                    max: 13,
                    message: 'Mobile No must be between 10 to 13 digits'
                },*/
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Mobile No. must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Mobile No can only consist of digits'
                }
            }
        },
        "weight": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Height  can only consist of characters'
                }
            }
        },

        "hair_and_eyes": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 100,
                    message: 'This field must between 1 to 100 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },
        "markings": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 100,
                    message: 'This field must between 1 to 100 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of characters'
                }
            }
        },
        "short_description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                /*stringLength: 
                {
                    min: 2,
                    max: 200,
                    message: 'This field must between 1 to 200 characters'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can not contain special characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },       
        "personal_info": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "education_history": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "bereavement_issues": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "drug_n_alcohol_issues": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },        
        "mental_health_issues": 
        {
            validators: 
            {
                /*notEmpty: 
                {
                  message: 'This field is required'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        }/*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/

    }  
});

$('#add_daily_record_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },/*,
                stringLength: {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can not contain special characters'
                }
            }
        },
        "score": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }
    }  
});

$('#edit_daily_record_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },/*,
                stringLength: {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "score": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }
    }  
});

$('#add_risk_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        /*"icon": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },*/
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field is required must between 1 to 2 digits'
                }
            }
        }
    }
});

$('#edit_risk_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        /*"icon": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },*/
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field is required must between 1 to 2 digits'
                }
            }
        }
    }
});

$('#add_earning_scheme_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 100,
                    message: 'This field is required must between 1 to 100 digits'
                }
            }
        },
        "icon": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field is required must between 1 to 30 digits'
                }

            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field is required must between 1 to 2 digits'
                }

            }
        }
    }
});

$('#edit_earning_scheme_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 100,
                    message: 'This field is required must between 1 to 100 digits'
                }

            }
        },
        /*"icon": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },*/
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field is required must between 1 to 2 digits'
                }
            }
        }
    }
});

$('#add_earning_scheme_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "icon": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    }
});

$('#edit_earning_scheme_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        /*"icon": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },*/
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    }
});

$('#add_incentive_earning_scheme_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        
        "details": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },

        "stars": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[0-9 \n]+$/,
                    message: 'This field can only consist of numeric characters'
                }
            }
        },

        "url": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&?/=:#$%!_+-.,'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },

        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    }
});

$('#edit_incentive_earning_scheme_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        
        "details": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },

        "stars": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[0-9 \n]+$/,
                    message: 'This field can only consist of numeric characters'
                }
            }
        },

        "url": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&?/=:#$%!_+-.,'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },

        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    }
});

$('#add_service_users_care_history_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "date": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    }
});
$('#edit_service_users_care_history_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "date": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    }
});

//admin su care team
$('#add_care_team_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "job_title_id": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 30,
                    message: 'This field must between 1 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'This field can only consist of digits and plus sign'
                }
            }
        },
        /*"email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        },*/
        "address": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        }/*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/                
    }  
});

$('#welcome_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "home":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "company":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }                
    }  
});

$('#add_homelist_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9 ]+$/,
                    message: 'Name can only consist of characters'
                }
            }
        },
        "location_history_duration": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[0-9]+$/,
                    message: 'This field should contain numbers only'
                }
            }
        },   
        "address": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&#+-.,'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        }/*,
        "security_policy":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                file: {
                        extension: 'pdf',
                        // type: 'security_policy/pdf',
                        // maxSize: 2097152,   // 2048 * 1024
                        message: 'The selected file is not valid'
                }
            }
        },*/          
    }  
});

$('#edit_homelist_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_ ]+$/,
                    message: 'Name can only consist of characters'
                }
            }
        },
        "location_history_duration":    
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[0-9]+$/,
                    message: 'This field should contain numbers only'
                }
            }
        },
        "address": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&#+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        }/*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        } */               
    }  
});

// admins_form
$('#add_system_admins_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'User Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_ ]+$/,
                    message: 'User Name can only consist of alphanumeric characters'
                },
                remote: 
                {
                   message: 'Username already exists',
                   url: 'check_user_username_exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
                }
            }
        },
        "password": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        },
        "company": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 100,
                    message: 'This fields must between 2 to 100 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_ ]+$/,
                    message: 'This field allows only alphanumeric characters'
                },  
                remote: 
                {
                   message: 'Company already exists',
                   url: 'check_user_company_exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
                }
            }
        }         
    }  
});

$('#edit_system_admins_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'User Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_ ]+$/,
                    message: 'User Name can only consist of alphanumeric characters'
                }
            }
        },
        "password": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        },
        "company": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 100,
                    message: 'This fields must between 2 to 100 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_ ]+$/,
                    message: 'This field allows only alphanumeric characters'
                }
            }
        }         
    }  
});

$('#edit_company_charge').formValidation({
    framework: 'bootstrap',
    live: 'disabled',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "start_range": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 8,
                    message: 'Maximum 8 digits are allowed.'
                },
                regexp: 
                {
                    regexp: /^[0-9.+]+$/,
                    message: 'Only digits are allowed'
                },
                remote: 
                {
                   message: 'Range overlaps previous range.',
                   url: host+'admin/company-charge/validate-home-range',
                   type: "post",
                   data:{
                        previous_range: function(){
                            return $("#previous_range").val();
                        },
                        start_range: function(){
                            return $("#start_range").val();
                        },
                        end_range: function(){
                            return $("#end_range").val();
                        },
                        package_type: function(){
                            return $("#package_type").val();
                        },
                    },
                }
            }
        },
        "end_range": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 8,
                    message: 'Maximum 8 digits are allowed.'
                },
                regexp: 
                {
                    regexp: /^[0-9.+]+$/,
                    message: 'Only digits are allowed'
                },
                remote: 
                {
                   message: 'Range end should be greater than range start.',
                   url: host+'admin/company-charge/validate-range-gap',
                   type: "post",
                   data:{
                        start_range: function(){
                            return $("#start_range").val();
                        },
                        end_range: function(){
                            return $("#end_range").val();
                        },
                    },
                }
            }
        },
        "price_monthly": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 8,
                    message: 'Maximum 8 digits are allowed.'
                },
                regexp: 
                {
                    regexp: /^[0-9.+]+$/,
                    message: 'Only digits are allowed'
                }
            }
        },
        "price_yearly": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 8,
                    message: 'Maximum 8 digits are allowed.'
                },
                regexp: 
                {
                    regexp: /^[0-9.+]+$/,
                    message: 'Only digits are allowed'
                }
            }
        },
        "days": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 8,
                    message: 'Maximum 8 digits are allowed.'
                },
                regexp: 
                {
                    regexp: /^[0-9+]+$/,
                    message: 'Only digits are allowed'
                }
            }
        }, 
    }  
});

$('#add_manager_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                },
            }
        },
      
        "contact_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Contact number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9+]+$/,
                    message: 'Contact number can only consist of digits'
                },
                // remote: 
                // {
                //    message: 'Contact number already exists.',
                //    url: host+'admin/manager/check-contact-no-exists',
                //    type: "post",
                //    dataType: 'json',
                //    data:{
                //         contact_no: function(){
                //             return $("#contact_no").val();
                //         },
                //         manager_id: function(){
                //             return $("#manager_id").val();
                //         },
                //         _token: function(){
                //             return $("#token").val();
                //         },
                //     },
                //     success:function(result){
                //         console.log("data");
                //         console.log(result);
                //     }
                // },
            }
        },
        "address":{
            validators:{
                notEmpty:{
                    message: 'This field is required'
                }
            }
        }
    }  
});

$('#edit_manager_form').formValidation({
    live: 'disabled',
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
   
        "contact_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Contact number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9+]+$/,
                    message: 'Contact number can only consist of digits'
                },
                // remote: 
                // {
                //    message: 'Contact number already exists.',
                //    url: host+'admin/manager/check-contact-no-exists',
                //    type: "post",
                //    dataType: 'json',
                //    data:{
                //         contact_no: function(){
                //             return $("#contact_no").val();
                //         },
                //         manager_id: function(){
                //             return $("#manager_id").val();
                //         },
                //         _token: function(){
                //             return $("#token").val();
                //         },
                //     },
                // },
            }
        },
        "address":{
            validators:{
                notEmpty:{
                    message: 'This field is required'
                }
            }
        }
    }  
});

$('#send_migration_request_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "new_home_id": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 50,
                    message: 'This field must between 1 to 50 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z0-9 ]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },        
        "company": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 50,
                    message: 'This field must between 1 to 50 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z0-9. ]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "reason": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 255,
                    message: 'This field must between 2 to 255 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }         
    }  
});

$('#sup_migration_update').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "new_status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z]+$/,
                    message: 'This field can only consist of alphabets'
                }
            }
        },
        "reply": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 255,
                    message: 'This field must between 2 to 255 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }         
    }  
});

 $('#add_living_skill_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: {
                    min: 2,
                    max: 255,
                    message: 'Description must between 2 to 255 alphanumeric characters'
                }
                // ,
                // regexp: 
                // {
                //     regexp: /^[A-Z,a-z,0-9()&+-.,:;?£/'" \n]+$/,
                //     message: 'This field can not contain special characters'
                // }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }
    }  
});

$('#edit_living_skill_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: {
                    min: 2,
                    max: 255,
                    message: 'Description must between 2 to 255 alphanumeric characters'
                }
                // ,
                // regexp: 
                // {
                //     regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                //     message: 'This field can only consist of alphanumeric characters'
                // }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }
    }  
});


// Add Education Training Background
$('#add_education_training_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {   
        "description":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                /*stringLength: 
                {
                    min: 2,
                    max: 200,
                    message: 'This field must between 2 to 200 characters'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }         
    }  
});

// Edit Education Training Form
$('#edit_education_training_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                /*stringLength: 
                {
                    min: 2,
                    max: 200,
                    message: 'This field must between 2 to 200 characters'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }         
    }  
});

// Add MFC Form 
$('#add_mfc_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {   
        "description":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                /*stringLength: 
                {
                    min: 2,
                    max: 200,
                    message: 'This field must between 2 to 200 characters'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }         
    }  
});

// Edit MFC Form
$('#edit_mfc_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {   
        "description":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                /*stringLength: 
                {
                    min: 2,
                    max: 200,
                    message: 'This field must between 2 to 200 characters'
                },*/
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field  can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        }         
    }  
});

$('#add_job_title_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Job Title must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Job Title can only consist of alphabets'
                }
            }
        }
    }
});

$('#edit_job_title_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Job Title must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Job Title can only consist of alphabets'
                }
            }
        }
    }
});

/*------- 19 June 2017-------*/
$('#add_mood_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }                

    }  
});

$('#edit_mood_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        // "image":
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         }
        //     }
        // },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }                

    }  
});
// $('#edit_mood_form').formValidation({
//     framework: 'bootstrap',
//     excluded: [':disabled'],
//     message: 'This value is not valid',
//     icon: 
//     {
//         valid: 'fa fa-check',
//         invalid: 'fa fa-times',
//         validating: 'fa fa-refresh'
//     },
//     err: 
//     {
//         container: 'popover'
//     },
//     fields:
//     {
//         "suggestions": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
                
//                 regexp: 
//                 {
//                     regexp: /^[A-Z,a-z,0-9&+-.,'" \n]+$/,
//                     message: 'Suggestions can only consist of alphabets'
//                 }
//             }
//         }         
//     }  
// });

/*--- 28 June(Avneet) ----*/

$('#edit_contact_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "reply": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'Reply can only consist of alphabets'
                }
            }
        }         
    }  
});


$('#admin_profile_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        // "user_name": 
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         },
        //         stringLength: 
        //         {
        //             min: 2,
        //             max: 30,
        //             message: 'User Name must between 2 to 30 characters'
        //         },
        //         regexp: 
        //         {
        //             regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
        //             message: 'User Name can only consist of alphanumeric characters'
        //         },
        //         remote: 
        //         {
        //            message: 'username already exists',
        //            url: 'check_admin_username',
        //            type: 'GET',
        //            delay: 2000     // Send Ajax request every 2 seconds
        //         }
        //     }
        // },

        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
               // },
               // remote: 
               // {
               //     message: 'Email already exists',
               //     url: 'check-user-email-exists',
               //     type: 'GET',
               //     delay: 2000     // Send Ajax request every 2 seconds
               // }
            }
        },

        "company": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Company must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Company can only consist of alphabets'
                }
            }
        }
    }  
});

$('#add_access_name_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Job Title must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Job Title can only consist of alphabets'
                }
            }
        }
    }
});

$('#edit_access_name_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Job Title must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Job Title can only consist of alphabets'
                }
            }
        }
    }
});


 $('#rota_shift_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "start_time": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }, 
        "end_time": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }         
    }  
});

$('#edit_daily_record_score_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        } 
    }  
});

$('#add_shift_plan').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Shift Name can only consist of alphabets'
                }
            }
        },
        "start_time": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }, 
        "end_time": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }         
    }  
});

$('#edit_shift_plan').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Shift Name can only consist of alphabets'
                }
            }
        },
        "start_time": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }, 
        "end_time": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }         
    }  
});

//////////////  July 15, 2017 -------  SYSTEM GUIDE  /////////////
$('#add_system_guide_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "question": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "answer": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }     
    }  
});

$('#add_system_guide_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "question": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "answer": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }     
    }  
});

$('#edit_system_guide_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "question": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "answer": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }     
    }  
});

///////////--------Add External Service(backEnd) -> Childs Section-------//////////////

$('#add_external_service_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "company_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Company name can only consist of alphabets'
                }
            }
        },
        "contact_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Company name can only consist of alphabets'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
               // },
               // remote: 
               // {
               //     message: 'Email already exists',
               //     url: 'check-user-email-exists',
               //     type: 'GET',
               //     delay: 2000     // Send Ajax request every 2 seconds
               // }
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        }         
    }  
});

///////////--------Edit External Service(backEnd) -> Childs Section-------//////////////
$('#edit_external_service_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "company_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Company name can only consist of alphabets'
                }
            }
        },
        "contact_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Company name can only consist of alphabets'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
               // },
               // remote: 
               // {
               //     message: 'Email already exists',
               //     url: 'check-user-email-exists',
               //     type: 'GET',
               //     delay: 2000     // Send Ajax request every 2 seconds
               // }
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        }         
    }  
});

// for superAdmin, user-add module, user_form.blade

$('#SuperAdminUserAddForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                     regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                     message: 'User Name can only consist of alphanumeric characters'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        }/*,
        "image": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/
    }
});
$('#SuperAdminUserEditForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                     regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                     message: 'User Name can only consist of alphanumeric characters'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        }/*,
        "image": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/
    }
});

//admin add module, admin_form.blade
$('#HomeAdminAddForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                     regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                     message: 'User Name can only consist of alphanumeric characters'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        }/*,
        "image": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/
    }
});

$('#HomeAdminEditForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                     regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                     message: 'User Name can only consist of alphanumeric characters'
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
            }
        }/*,
        "image": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }*/
    }
});

$('#FileManagerCategoryAdd').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,&,0-9 ]+$/,
                    message: 'Name can only consist of alphanumeric'
                }
            }
        }
    }
});

$('#FileManagerCategoryEdit').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,&,0-9 ]+$/,
                    message: 'Name can only consist of alphanumeric'
                }
            }
        }
    }
});

$('#SuperAdminSocailAppAddForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }
    }
});

$('#SuperAdminSocailAppEditForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }
    }
});

$('#AddUserTaskAllocation').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }

    }
});

$('#EditUserTaskAllocation').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }

    }
});

$('#AddUserSickLeave').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "leave_date": 
        {
            validators: 
            {
                // notEmpty: 
                // {
                //   message: 'This field is required'
                // },
                // date: {
                //     format: 'MM/DD/YYYY',
                //     message: 'The date is not a valid'
                // }
                // date: {
                //     format: 'dd-mm-yyyy',
                //     message: 'The format is dd-mm-yyyy'
                // },
                notEmpty: {
                    message: 'The field can not be empty'
                }
            }
        },
        "reason": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }

    }
});

$('#EditUserSickLeave').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "leave_date": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                date: {
                    format: 'MM/DD/YYYY',
                    message: 'The date is not a valid'
                }
            }
        },
        "reason": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }

    }
});

$('#SuperAdminEthnicityAddForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }
    }
});

$('#SuperAdminEthnicityEditForm').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }
    }
});

$('#AddUserAnnualLeave').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        // "leave_date": 
        // {
        //     validators: 
        //     {
        //         // notEmpty: 
        //         // {
        //         //   message: 'This field is required'
        //         // },
        //         // date: {
        //         //     format: 'MM/DD/YYYY',
        //         //     message: 'The date is not a valid'
        //         // }
        //         // date: {
        //         //     format: 'dd-mm-yyyy',
        //         //     message: 'The format is dd-mm-yyyy'
        //         // },
        //         notEmpty: {
        //             message: 'The field can not be empty'
        //         }
        //     }
        // },
        "reason": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }

    }
});

$('#EditUserAnnualLeave').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        // "leave_date": 
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         },
        //         date: {
        //             format: 'MM/DD/YYYY',
        //             message: 'The date is not a valid'
        //         }
        //     }
        // },
        "reason": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        }

    }

});

//Sick Leave Sanction form
$('#sanction_leave_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "sanction_leave": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
            }
        },
        "home_id": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
            }
        },
        "staff_user_id": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
            }
        },
        "date":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                },

            }
        },
    }  
});

$('#add_company_manager_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        "user_name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'User Name must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9,_,@,# ]+$/,
                    message: 'User Name can only consist of alphanumeric characters'
                },
                remote: 
                {
                   message: 'User Name already exists',
                   url: 'check_username_unique',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
                }
            }
        },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }
               // },
               // remote: 
               // {
               //     message: 'Email already exists',
               //     url: 'check-user-email-exists',
               //     type: 'GET',
               //     delay: 2000     // Send Ajax request every 2 seconds
               // }
            }
        },
        "job_title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: "This field can only consist of 'A-Z,a-z,0-9 &-' characters"
                }
            }
        },
      
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "payroll": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "holiday_entitlement": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of alphanumeric characters'
                }
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        },
        "date_of_joining":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "date_of_leaving":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "current_location":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "personal_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "banking_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "company_id[]":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
        /*,
        "image":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }  */              

    }  
});

$('#edit_company_manager_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z ]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        },
        // "user_name": 
        // {
        //     validators: 
        //     {
        //         notEmpty: 
        //         {
        //           message: 'This field is required'
        //         },
        //         stringLength: 
        //         {
        //             min: 2,
        //             max: 30,
        //             message: 'User Name must between 2 to 30 characters'
        //         },
        //         regexp: 
        //         {
        //             regexp : /^[A-Z,a-z,0-9,_,#,@ ]+$/,
        //             message: 'User Name can only consist of alphanumeric characters'
        //         },
        //         remote:     
        //         {   
        //            message: 'User Name already exists',
        //            url    : '/scits/admin/users/edit/username_exists',
        //            type   : 'GET',
        //            delay  : 2000     // Send Ajax request every 2 seconds
        //         }
        //     }
        // },
        "email": 
        {
            validators: 
            {
               notEmpty: 
               {
                  message: 'This field is required'
               },
               emailAddress: 
               {
                  message: 'The value is not a valid email address'
               },
               regexp: 
               {
                  regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                  message: 'The value is not a valid email address'
               }/*,
               remote: 
               {
                   message: 'Email already exists',
                   url: 'check-user-email-exists',
                   type: 'GET',
                   delay: 2000     // Send Ajax request every 2 seconds
               }*/
            }
        },
        "job_title": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'This field must between 2 to 30 characters'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                    message: 'This field can only consist of characters'
                }
            }
        },
        "phone_no": 
        {
            validators: 
            {
                notEmpty: 
                {
                    message: 'This field is required'
                },
                stringLength: 
                {
                    min: 8,
                    // max: 13,
                    message: 'Phone number must contain atleast 8 digits'
                },
                regexp: 
                {
                    regexp: /^[0-9 +]+$/,
                    message: 'Phone number can only consist of digits'
                }
            }
        },
        "description": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            }
        },
        "payroll": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            }
        },
        "holiday_entitlement": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            },
            regexp: 
            {
                regexp: /^[A-Z,a-z,0-9&+-.,:;?£/'" \n]+$/,
                message: 'This field can only consist of alphanumeric characters'
            },
            stringLength: 
            {
                min: 1,
                max: 100,
                message: 'This field can contain between 1 to 100 characters'
            }
        },
        "status": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 1,
                    max: 2,
                    message: 'This field can contain between 1 to 2 characters'
                }
            }
        },
        "date_of_joining":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                },
                
            }
        },
        "date_of_leaving":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "current_location":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "personal_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "banking_info":
        {
            validators:
            {
                notEmpty:
                {
                    message: 'This field is required'
                }
            }
        },
        "company_id[]":
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                }
            }
        }
    } 
});
//Card Form



// $('#add_admin_card_detail').formValidation({
//     framework: 'bootstrap',
//     excluded: [':disabled'],
//     message: 'This value is not valid',
//     icon: 
//     {
//         valid: 'fa fa-check',
//         invalid: 'fa fa-times',
//         validating: 'fa fa-refresh'
//     },
//     err: 
//     {
//         container: 'popover'
//     },
//     fields:
//     {
//         "card_holder_name": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
//                 stringLength: 
//                 {
//                     min: 2,
//                     max: 30,
//                     message: 'Name must between 2 to 30 alphabets'
//                 },
//                 regexp: 
//                 {
//                     regexp: /^[A-Z,a-z ]+$/,
//                     message: 'Name can only consist of alphabets'
//                 },
//             }
//         },
//         "card_number": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                     message: 'This field is required'
//                 },
//                 stringLength: 
//                 {
//                     min: 16,
//                     max: 16,
//                     message: 'Card number must contain 16 digits'
//                 },
//                 regexp: 
//                 {
//                     regexp: /^[0-9+]+$/,
//                     message: 'Card number can only consist of digits'
//                 }
//             }
//         },
//         "mm_yy":{
//             validators:{
//                 notEmpty:{
//                     message: 'This field is required'
//                 },
//                 regexp:
//                 {
//                     regexp: /^[0-9/]+$/,
//                     message: 'MM/YY can only consist of digits and slash'
//                 },
//                 stringLength:
//                 {
//                     min:5,
//                     max:5,
//                     message: "MM/YY must contain 5 characters"
//                 }
//             }
//         },
//         "cvv": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                     message: 'This field is required'
//                 },
//                 stringLength: 
//                 {
//                     min: 3,
//                     max: 3,
//                     message: 'Cvv number must contain 3 digits'
//                 },
//                 regexp: 
//                 {
//                     regexp: /^[0-9+]+$/,
//                     message: 'Cvv number can only consist of digits'
//                 }
//             }
//         },
//         "f_name": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
//                 stringLength: 
//                 {
//                     min: 2,
//                     max: 30,
//                     message: 'Name must between 2 to 30 alphabets'
//                 },
//                 regexp: 
//                 {
//                     regexp: /^[A-Z,a-z ]+$/,
//                     message: 'Name can only consist of alphabets'
//                 },
//             }
//         },
//         "l_name": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
//                 stringLength: 
//                 {
//                     min: 2,
//                     max: 30,
//                     message: 'Name must between 2 to 30 alphabets'
//                 },
//                 regexp: 
//                 {
//                     regexp: /^[A-Z,a-z ]+$/,
//                     message: 'Name can only consist of alphabets'
//                 },
//             }
//         },
//         "street": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
//             }
//         },
//         "state_code": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
//             }
//         },
//         "city_name": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                   message: 'This field is required'
//                 },
//             }
//         },
//         "zip_code": 
//         {
//             validators: 
//             {
//                 notEmpty: 
//                 {
//                     message: 'This field is required'
//                 },
//                 stringLength: 
//                 {
//                     min: 5,
//                     max: 5,
//                     message: 'This field must contain 5 digits'
//                 },
//                 regexp: 
//                 {
//                     regexp: /^[0-9+]+$/,
//                     message: 'This field can only consist of digits'
//                 }
//             }
//         },
//     }  
// });

$('#add_earning_scheme_label_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z \/]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        }
    }  
});

$('#edit_earning_scheme_label_form').formValidation({
    framework: 'bootstrap',
    excluded: [':disabled'],
    message: 'This value is not valid',
    icon: 
    {
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
    },
    err: 
    {
        container: 'popover'
    },
    fields:
    {
        "name": 
        {
            validators: 
            {
                notEmpty: 
                {
                  message: 'This field is required'
                },
                stringLength: 
                {
                    min: 2,
                    max: 30,
                    message: 'Name must between 2 to 30 alphabets'
                },
                regexp: 
                {
                    regexp: /^[A-Z,a-z \/]+$/,
                    message: 'Name can only consist of alphabets'
                }
            }
        }
    }  
});
