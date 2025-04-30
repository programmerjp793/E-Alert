package com.example.ealert.app

import android.app.Activity
import android.app.AlertDialog
import android.app.DatePickerDialog
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.provider.MediaStore
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import com.example.ealert.R
import com.example.ealert.app.model.UserProfile
import com.example.ealert.app.response.ApiResponse
import com.example.ealert.app.retrofit.RetrofitClient
import com.google.android.material.imageview.ShapeableImageView
import okhttp3.MediaType
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.io.File
import java.util.Calendar

class ProfileActivity : AppCompatActivity() {

    private lateinit var etFirstName: EditText
    private lateinit var etMiddleName: EditText
    private lateinit var etLastName: EditText
    private lateinit var etBirthdate: EditText
    private lateinit var spinnerGender: Spinner
    private lateinit var etIdType: EditText
    private lateinit var etIdNumber: EditText
    private lateinit var etPhoneNo: EditText
    private lateinit var etLandline: EditText
    private lateinit var etAddress: EditText
    private lateinit var btnSubmit: Button
    private lateinit var ivIdImage: ShapeableImageView
    private lateinit var ivVerifyId: ImageView
    private lateinit var btnEditProfileImage: ImageButton
    private lateinit var btnUploadVerifyId: Button
    private lateinit var btnBack: ImageButton

    private var selectedImageUri: Uri? = null
    private var selectedVerifyIdUri: Uri? = null
    private var requireVerifyId: Boolean = false

    companion object {
        private const val PICK_IMAGE_REQUEST = 1001
        private const val PICK_VERIFY_ID_REQUEST = 1002
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_profile)

        // Initialize views
        initializeViews()

        // Get extras and populate fields
        requireVerifyId = intent.getBooleanExtra("require_verify_id", false)
        populateFieldsFromExtras()

        // If verify ID is required, show a dialog
        if (requireVerifyId) {
            AlertDialog.Builder(this)
                .setTitle("Verification Required")
                .setMessage("Please upload your verification ID to continue using the app.")
                .setPositiveButton("OK") { dialog, _ ->
                    dialog.dismiss()
                    // Scroll to verify ID section
                    scrollToVerifyId()
                }
                .setCancelable(false)
                .show()
        }

        // Populate gender spinner
        val genderOptions = arrayOf("Male", "Female")
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, genderOptions)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerGender.adapter = adapter

        // Set click listeners
        etBirthdate.setOnClickListener { showDatePickerDialog() }
        btnEditProfileImage.setOnClickListener { pickImage() }
        btnUploadVerifyId.setOnClickListener { pickVerifyId() }
        btnSubmit.setOnClickListener { submitProfile() }
        btnBack.setOnClickListener { 
            if (requireVerifyId && selectedVerifyIdUri == null) {
                AlertDialog.Builder(this)
                    .setTitle("Verification Required")
                    .setMessage("You must upload a verification ID before leaving this screen.")
                    .setPositiveButton("OK", null)
                    .show()
            } else {
                finish()
            }
        }
    }

    private fun initializeViews() {
        etFirstName = findViewById(R.id.etFirstName)
        etMiddleName = findViewById(R.id.etMiddleName)
        etLastName = findViewById(R.id.etLastName)
        etBirthdate = findViewById(R.id.etBirthdate)
        spinnerGender = findViewById(R.id.spinnerGender)
        etIdType = findViewById(R.id.etIdType)
        etIdNumber = findViewById(R.id.etIdNumber)
        etPhoneNo = findViewById(R.id.etPhoneNo)
        etLandline = findViewById(R.id.etLandline)
        etAddress = findViewById(R.id.etAddress)
        btnSubmit = findViewById(R.id.btnSubmit)
        ivIdImage = findViewById(R.id.ivIdImage)
        ivVerifyId = findViewById(R.id.ivVerifyId)
        btnEditProfileImage = findViewById(R.id.btnEditProfileImage)
        btnUploadVerifyId = findViewById(R.id.btnUploadVerifyId)
        btnBack = findViewById(R.id.btnBack)
    }

    private fun populateFieldsFromExtras() {
        intent?.let { intent ->
            etFirstName.setText(intent.getStringExtra("firstname"))
            etMiddleName.setText(intent.getStringExtra("middlename"))
            etLastName.setText(intent.getStringExtra("lastname"))
            etBirthdate.setText(intent.getStringExtra("birthdate"))
            etIdType.setText(intent.getStringExtra("id_type"))
            etIdNumber.setText(intent.getStringExtra("id_number"))
            etPhoneNo.setText(intent.getStringExtra("mobile_no"))
            etLandline.setText(intent.getStringExtra("landline"))
            etAddress.setText(intent.getStringExtra("address"))

            // Set gender in spinner
            intent.getStringExtra("gender")?.let { gender ->
                val position = when (gender.lowercase()) {
                    "male" -> 0
                    "female" -> 1
                    else -> 0
                }
                spinnerGender.setSelection(position)
            }

            // Load ID image if available
            intent.getStringExtra("id_image")?.let { imageUrl ->
                if (imageUrl.isNotEmpty()) {
                    com.squareup.picasso.Picasso.get()
                        .load("${RetrofitClient.BASE_URL}$imageUrl")
                        .placeholder(R.drawable.ic_user_placeholder)
                        .error(R.drawable.ic_user_placeholder)
                        .into(ivIdImage)
                }
            }
        }
    }

    private fun showDatePickerDialog() {
        val calendar = Calendar.getInstance()
        val year = calendar.get(Calendar.YEAR)
        val month = calendar.get(Calendar.MONTH)
        val day = calendar.get(Calendar.DAY_OF_MONTH)

        val datePickerDialog = DatePickerDialog(
            this,
            { _, selectedYear, selectedMonth, selectedDay ->
                val formattedDate = "$selectedYear-${selectedMonth + 1}-$selectedDay"
                etBirthdate.setText(formattedDate)
            },
            year, month, day
        )
        datePickerDialog.show()
    }

    private fun pickImage() {
        val intent = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            Intent(MediaStore.ACTION_PICK_IMAGES)
        } else {
            Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI)
        }
        startActivityForResult(intent, PICK_IMAGE_REQUEST)
    }

    private fun pickVerifyId() {
        val intent = Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI)
        startActivityForResult(intent, PICK_VERIFY_ID_REQUEST)
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == Activity.RESULT_OK && data != null) {
            when (requestCode) {
                PICK_IMAGE_REQUEST -> {
                    selectedImageUri = data.data
                    ivIdImage.setImageURI(selectedImageUri)
                }
                PICK_VERIFY_ID_REQUEST -> {
                    selectedVerifyIdUri = data.data
                    ivVerifyId.setImageURI(selectedVerifyIdUri)
                }
            }
        }
    }

    private fun getRealPathFromUri(uri: Uri): String {
        val cursor = contentResolver.query(uri, null, null, null, null)
        cursor?.moveToFirst()
        val idx = cursor?.getColumnIndex(MediaStore.Images.ImageColumns.DATA)
        val path = idx?.let { cursor.getString(it) }
        cursor?.close()
        return path ?: ""
    }

    private fun createPartFromString(descriptionString: String): RequestBody {
        return RequestBody.create(
            MediaType.parse("text/plain"),
            descriptionString
        )
    }

    private fun submitProfile() {
        // Validate required fields
        if (etFirstName.text.isNullOrEmpty() || etLastName.text.isNullOrEmpty() || etPhoneNo.text.isNullOrEmpty()) {
            Toast.makeText(this, "Please fill in all required fields", Toast.LENGTH_SHORT).show()
            return
        }

        val sharedPreferences = getSharedPreferences("user_prefs", MODE_PRIVATE)
        val userIdValue = sharedPreferences.getInt("user_id", -1)
        val userId = createPartFromString(userIdValue.toString())

        val firstName = createPartFromString(etFirstName.text.toString())
        val middleName = createPartFromString(etMiddleName.text.toString())
        val lastName = createPartFromString(etLastName.text.toString())
        val birthDate = createPartFromString(etBirthdate.text.toString())
        val gender = createPartFromString(spinnerGender.selectedItem.toString())
        val idType = createPartFromString(etIdType.text.toString())
        val idNumber = createPartFromString(etIdNumber.text.toString())
        val mobileNo = createPartFromString(etPhoneNo.text.toString())
        val landline = createPartFromString(etLandline.text.toString())
        val address = createPartFromString(etAddress.text.toString())

        // Create multipart for ID image if selected
        var idImagePart: MultipartBody.Part? = null
        selectedImageUri?.let { uri ->
            val file = File(getRealPathFromUri(uri))
            val requestFile = RequestBody.create(MediaType.parse("image/*"), file)
            idImagePart = MultipartBody.Part.createFormData("id_image", file.name, requestFile)
        }

        // Create multipart for verify ID if selected
        var verifyIdPart: MultipartBody.Part? = null
        selectedVerifyIdUri?.let { uri ->
            val file = File(getRealPathFromUri(uri))
            val requestFile = RequestBody.create(MediaType.parse("image/*"), file)
            verifyIdPart = MultipartBody.Part.createFormData("verify_id", file.name, requestFile)
        }

        // Make API call
        val apiService = RetrofitClient.instance
        val call = apiService.updateUserProfile(
            userId = userId,
            firstName = firstName,
            middleName = middleName,
            lastName = lastName,
            birthDate = birthDate,
            gender = gender,
            idType = idType,
            idNumber = idNumber,
            mobileNo = mobileNo,
            landline = landline,
            address = address,
            idImage = idImagePart,
            verifyId = verifyIdPart
        )
        call.enqueue(object : Callback<ApiResponse> {
            override fun onResponse(call: Call<ApiResponse>, response: Response<ApiResponse>) {
                if (response.isSuccessful && response.body()?.status == "success") {
                    Toast.makeText(this@ProfileActivity, "Profile updated successfully", Toast.LENGTH_SHORT).show()
                    
                    // Navigate to HomeActivity
                    val intent = Intent(this@ProfileActivity, HomeActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                    startActivity(intent)
                    finish()
                } else {
                    Toast.makeText(this@ProfileActivity, response.body()?.message ?: "Failed to update profile", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<ApiResponse>, t: Throwable) {
                Toast.makeText(this@ProfileActivity, "Error: ${t.message}", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun scrollToVerifyId() {
        val scrollView = findViewById<ScrollView>(R.id.scrollView)
        val verifyIdSection = findViewById<View>(R.id.verifyIdSection)
        scrollView.post {
            scrollView.smoothScrollTo(0, verifyIdSection.top)
        }
    }

    override fun onBackPressed() {
        if (requireVerifyId && selectedVerifyIdUri == null) {
            AlertDialog.Builder(this)
                .setTitle("Verification Required")
                .setMessage("You must upload a verification ID before leaving this screen.")
                .setPositiveButton("OK", null)
                .show()
        } else {
            super.onBackPressed()
        }
    }
}
