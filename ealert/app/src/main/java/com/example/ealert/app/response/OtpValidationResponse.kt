package com.example.ealert.app.response

data class OtpValidationResponse(
    val status: String,
    val message: String?,
    val verification_token: String?
)