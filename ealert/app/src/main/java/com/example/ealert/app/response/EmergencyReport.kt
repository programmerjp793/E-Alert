package com.example.ealert.app.response

import com.google.gson.annotations.SerializedName

data class EmergencyReport(
    @SerializedName("id")
    val id: String,

    @SerializedName("reported_by")
    val reportedBy: String,

    @SerializedName("subject_report")
    val subject: String,

    @SerializedName("client_inquiry")
    val clientInquiry: String?,

    @SerializedName("address")
    val address: String?,

    @SerializedName("emergency_type")
    val emergencyType: String,

    @SerializedName("photo_attachment")
    val photoAttachment: String?,

    @SerializedName("location")
    val location: String?,

    @SerializedName("latitude")
    val latitude: String?,

    @SerializedName("longitude")
    val longitude: String?,

    @SerializedName("report_date")
    val dateTimeReported: String,

    @SerializedName("report_status")
    val status: String,

    @SerializedName("responder_id")
    val responderId: String?,

    @SerializedName("responder_username")
    val responderUsername: String?,

    @SerializedName("responder_date")
    val responderDate: String?,

    @SerializedName("responder_status")
    val responderStatus: String?,

    @SerializedName("emergency_agencies")
    val emergencyAgencies: String?,

    @SerializedName("responder_response")
    val responderResponse: String?
)
