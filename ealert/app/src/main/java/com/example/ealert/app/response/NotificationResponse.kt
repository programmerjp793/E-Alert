package com.example.ealert.app.response

import com.google.gson.annotations.SerializedName

data class Notification(
    @SerializedName("id") val id: Int,
    @SerializedName("username") val username: String,
    @SerializedName("emergency_type") val emergencyType: String,
    @SerializedName("subject_report") val subjectReport: String,
    @SerializedName("status") val status: String,
    @SerializedName("responder_username") val responderUsername: String,
    @SerializedName("responses") val responses: String?,
    @SerializedName("response_date") val responseDate: String,
    @SerializedName("emergency_agencies") val emergencyAgencies: String,
)

data class NotificationResponse(
    @SerializedName("status") val status: String,
    @SerializedName("message") val message: String?,
    @SerializedName("user_id") val userId: Int?,
    @SerializedName("username") val username: String?,
    @SerializedName("data") val data: List<Notification>?,
    @SerializedName("timestamp") val timestamp: String?
)