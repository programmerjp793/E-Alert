package com.example.ealert.app

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageButton
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.example.ealert.R
import com.example.ealert.app.response.ApiResponse
import com.example.ealert.app.retrofit.RetrofitClient
import com.google.android.material.imageview.ShapeableImageView
import com.squareup.picasso.Picasso
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ProfileFragment : Fragment() {
    private lateinit var tvFullName: TextView
    private lateinit var tvBirthdate: TextView
    private lateinit var tvGender: TextView
    private lateinit var tvIdType: TextView
    private lateinit var tvIdNumber: TextView
    private lateinit var tvPhoneNo: TextView
    private lateinit var tvLandline: TextView
    private lateinit var tvAddress: TextView
    private lateinit var ivProfileImage: ShapeableImageView
    private lateinit var ivVerifyIdPreview: ImageView

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.fragment_profile, container, false)
        initializeViews(view)
        fetchUserProfile()
        return view
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        // Initialize views
        ivVerifyIdPreview = view.findViewById(R.id.ivVerifyIdPreview)
    }

    private fun initializeViews(view: View) {
        tvFullName = view.findViewById(R.id.tvFullName)
        tvBirthdate = view.findViewById(R.id.tvBirthdate)
        tvGender = view.findViewById(R.id.tvGender)
        tvIdType = view.findViewById(R.id.tvIdType)
        tvIdNumber = view.findViewById(R.id.tvIdNumber)
        tvPhoneNo = view.findViewById(R.id.tvPhoneNo)
        tvLandline = view.findViewById(R.id.tvLandline)
        tvAddress = view.findViewById(R.id.tvAddress)
        ivProfileImage = view.findViewById(R.id.ivProfileImage)
    }

    private fun fetchUserProfile() {
        val sharedPreferences = requireActivity().getSharedPreferences("user_prefs", android.content.Context.MODE_PRIVATE)
        val userId = sharedPreferences.getInt("user_id", -1)

        if (userId == -1) {
            Toast.makeText(context, "User not logged in", Toast.LENGTH_SHORT).show()
            return
        }

        val apiService = RetrofitClient.instance
        val call = apiService.getUserProfile(userId)

        call.enqueue(object : Callback<ApiResponse> {
            override fun onResponse(call: Call<ApiResponse>, response: Response<ApiResponse>) {
                if (response.isSuccessful && response.body()?.status == "success") {
                    response.body()?.let {
                        displayUserProfile(it)
                    }
                } else {
                    Toast.makeText(context, "Failed to fetch profile", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<ApiResponse>, t: Throwable) {
                Toast.makeText(context, "Error: ${t.message}", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun displayUserProfile(user: ApiResponse) {
        tvFullName.text = "${user.firstname} ${user.middlename} ${user.lastname}"
        tvBirthdate.text = user.birthdate ?: "Not set"
        tvGender.text = user.gender ?: "Not set"
        tvIdType.text = user.id_type ?: "Not set"
        tvIdNumber.text = user.id_number ?: "Not set"
        tvPhoneNo.text = user.mobile_no ?: "Not set"
        tvLandline.text = user.landline ?: "Not set"
        tvAddress.text = user.address ?: "Not set"

        // Check if verify_id is not set and redirect to ProfileActivity
        if (user.verify_id.isNullOrEmpty()) {
            Toast.makeText(requireContext(), "Please upload your verification ID", Toast.LENGTH_LONG).show()
            val intent = Intent(requireContext(), ProfileActivity::class.java).apply {
                putExtra("firstname", user.firstname)
                putExtra("middlename", user.middlename)
                putExtra("lastname", user.lastname)
                putExtra("birthdate", user.birthdate)
                putExtra("gender", user.gender)
                putExtra("id_type", user.id_type)
                putExtra("id_number", user.id_number)
                putExtra("mobile_no", user.mobile_no)
                putExtra("landline", user.landline)
                putExtra("address", user.address)
                putExtra("id_image", user.id_image)
                putExtra("require_verify_id", true)
            }
            startActivity(intent)
            return
        }

        // Load profile image if available
        user.id_image?.let { imageUrl ->
            if (imageUrl.isNotEmpty()) {
                Picasso.get()
                    .load("${RetrofitClient.BASE_URL}$imageUrl")
                    .placeholder(R.drawable.ic_user_placeholder)
                    .error(R.drawable.ic_user_placeholder)
                    .into(ivProfileImage)
            }
        }

        // Load verify ID image if available
        user.verify_id?.let { verifyIdUrl ->
            if (verifyIdUrl.isNotEmpty()) {
                Picasso.get()
                    .load("${RetrofitClient.BASE_URL}$verifyIdUrl")
                    .placeholder(R.drawable.ic_user_placeholder)
                    .error(R.drawable.ic_user_placeholder)
                    .into(ivVerifyIdPreview)
            }
        }
    }
}