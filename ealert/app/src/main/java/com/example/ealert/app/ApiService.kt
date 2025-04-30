package com.example.ealert.app

import com.example.ealert.app.response.ApiResponse
import com.example.ealert.app.response.EmergencyReport
import com.example.ealert.app.response.EmergencyReportResponse
import com.example.ealert.app.response.NotificationResponse
import com.example.ealert.app.response.OtpValidationResponse
import com.example.ealert.app.response.ReportHistoryResponse
import okhttp3.MultipartBody
import okhttp3.RequestBody
import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.Response
import retrofit2.http.*
import retrofit2.http.POST
import retrofit2.http.Part

interface ApiService {
    @FormUrlEncoded
    @POST("user_login.php")
    fun loginUser(
        @Field("email") email: String,
        @Field("password") password: String
    ): Call<ApiResponse>

    @FormUrlEncoded
    @POST("user_logout.php")
    fun logoutUser(
        @Field("user_id") userId: Int
    ): Call<ResponseBody>

    @FormUrlEncoded
    @POST("user_signup.php")
    fun registerUser(
        @Field("username") username: String,
        @Field("email") email: String,
        @Field("password") password: String,
        @Field("confirm_password") confirmPassword: String
    ): Call<ResponseBody>

    @FormUrlEncoded
    @POST("profile_save.php")
    fun getUserProfile(
        @Field("user_id") userId: Int,
        @Field("action") action: String = "get_profile"
    ): Call<ApiResponse>

    @Multipart
    @POST("profile_save.php")
    fun updateUserProfile(
        @Part("user_id") userId: RequestBody,
        @Part("firstname") firstName: RequestBody,
        @Part("middlename") middleName: RequestBody,
        @Part("lastname") lastName: RequestBody,
        @Part("birthdate") birthDate: RequestBody,
        @Part("gender") gender: RequestBody,
        @Part("id_type") idType: RequestBody,
        @Part("id_number") idNumber: RequestBody,
        @Part("mobile_no") mobileNo: RequestBody,
        @Part("landline") landline: RequestBody,
        @Part("address") address: RequestBody,
        @Part idImage: MultipartBody.Part?,
        @Part verifyId: MultipartBody.Part?
    ): Call<ApiResponse>

    @GET("reporthistory.php")
    suspend fun getReportHistory(@Query("user_id") userId: Int): Response<ReportHistoryResponse>

    // Non-suspend version alternative
    @GET("reporthistory.php")
    fun getReportHistoryCall(@Query("user_id") userId: Int): Call<ReportHistoryResponse>

    @Multipart
    @POST("process_report.php")
    suspend fun submitReport(
        @Part("user_id") userId: RequestBody,
        @Part("subject_report") subjectReport: RequestBody,
        @Part("emergency_type") emergencyType: RequestBody,
        @Part("location") location: RequestBody,
        @Part("latitude") latitude: RequestBody,
        @Part("longitude") longitude: RequestBody,
        @Part("client_inquiry") clientInquiry: RequestBody,
        @Part photo: MultipartBody.Part?
    ): Response<ApiResponse>

    @GET("notifications.php")
    fun getNotifications(@Query("user_id") userId: Int): Call<NotificationResponse>

    @FormUrlEncoded
    @POST("password_reset.php?action=verify_email")
    suspend fun verifyEmail(
        @Field("email") email: String
    ): Response<ApiResponse>

    @FormUrlEncoded
    @POST("password_reset.php?action=reset_password")
    suspend fun resetPassword(
        @Field("token") verificationToken: String,
        @Field("password") newPassword: String
    ): Response<ApiResponse>

}