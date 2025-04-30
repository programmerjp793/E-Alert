package com.example.ealert.app.response


data class ApiResponse(
    val status: String,
    val message: String?,
    val user_id: Int?,
    val username: String?,
    val email: String?,
    val active: Int?,
    val firstname: String?,
    val middlename: String?,
    val lastname: String?,
    val birthdate: String?,
    val gender: String?,
    val id_type: String?,
    val id_number: String?,
    val mobile_no: String?,
    val landline: String?,
    val address: String?,
    val id_image: String?,
    val verify_id: String?,
    val token: String?
)
